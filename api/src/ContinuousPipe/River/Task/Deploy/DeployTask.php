<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\ComponentStatus;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\TaskQueued;
use ContinuousPipe\River\Tide\Configuration\WildcardObject;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class DeployTask extends EventDrivenTask
{
    /**
     * Name of the task.
     *
     * @var string
     */
    const NAME = 'deploy';

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var DeployContext
     */
    private $context;

    /**
     * @var DeployTaskConfiguration
     */
    private $configuration;

    /**
     * @var Deployment|null
     */
    private $startedDeployment;

    /**
     * @var Client\PublicEndpoint[]
     */
    private $publicEndpoints = [];

    /**
     * @param EventCollection         $events
     * @param MessageBus              $commandBus
     * @param LoggerFactory           $loggerFactory
     * @param DeployContext           $context
     * @param DeployTaskConfiguration $configuration
     */
    public function __construct(EventCollection $events, MessageBus $commandBus, LoggerFactory $loggerFactory, DeployContext $context, DeployTaskConfiguration $configuration)
    {
        parent::__construct($context, $events);

        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->context = $context;
        $this->configuration = $configuration;
    }

    public function startDeployment(Tide $tide, DeploymentRequestFactory $deploymentRequestFactory, Client\Client $pipeClient)
    {
        $tideLogger = $this->loggerFactory->from($this->context->getLog());
        $taskLogger = $tideLogger->child(new Text(sprintf('Deploying environment (%s)', $this->getLabel())));
        $log = $taskLogger->getLog();

        $this->context->setTaskLog($log);
        $this->events->raiseAndApply(TaskQueued::fromContext($this->context));

        try {
            $deploymentRequest = $deploymentRequestFactory->create(
                $tide,
                new TaskDetails($this->context->getTaskId(), $log->getId()),
                $this->configuration
            );
        } catch (DeploymentRequestException $e) {
            $taskLogger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);

            throw $e;
        }

        try {
            $deployment = $pipeClient->start($deploymentRequest, $tide->getUser());

            $this->events->raiseAndApply(new DeploymentStarted(
                $this->context->getTideUuid(),
                $deployment,
                $this->getIdentifier()
            ));
        } catch (\Exception $e) {
            $this->events->raiseAndApply(new DeploymentFailed(
                $this->context->getTideUuid(),
                new Client\Deployment(Uuid::fromString(Uuid::NIL), $deploymentRequest, Client\Deployment::STATUS_FAILURE),
                $this->getIdentifier()
            ));

            $tideLogger->child(new Text(sprintf(
                'Something went wrong when starting the deployment: %s',
                $e->getMessage()
            )));
        }
    }

    public function receiveDeploymentNotification(Deployment $deployment)
    {
        if (null === $this->startedDeployment || $deployment->getUuid() != $this->startedDeployment->getUuid()) {
            return;
        }

        if ($deployment->isSuccessful()) {
            $this->events->raiseAndApply(new DeploymentSuccessful(
                $this->context->getTideUuid(),
                $deployment,
                $this->getIdentifier()
            ));
        } elseif ($deployment->isFailed()) {
            $this->events->raiseAndApply(new DeploymentFailed(
                $this->context->getTideUuid(),
                $deployment,
                $this->getIdentifier()
            ));
        }
    }

    public function apply(TideEvent $event)
    {
        parent::apply($event);

        if ($event instanceof DeploymentStarted) {
            $this->startedDeployment = $event->getDeployment();
        } elseif ($event instanceof DeploymentSuccessful) {
            $this->publicEndpoints = array_merge($this->publicEndpoints, $event->getDeployment()->getPublicEndpoints());
        }
    }

    public function accept(TideEvent $event)
    {
        if ($event instanceof DeploymentSuccessful || $event instanceof DeploymentFailed) {
            if (null === $event->getTaskId()) {
                return $this->isStarted() && $this->getStartedEvent()->getDeployment()->getUuid()->equals($event->getDeployment()->getUuid());
            }
        }

        return parent::accept($event);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return 1 <= $this->numberOfEventsOfType(DeploymentSuccessful::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return 0 < $this->numberOfEventsOfType(DeploymentFailed::class);
    }

    /**
     * @return bool
     */
    private function isStarted()
    {
        return 0 < $this->numberOfEventsOfType(DeploymentStarted::class);
    }

    /**
     * @return DeploymentStarted
     */
    private function getStartedEvent()
    {
        return $this->getEventsOfType(DeploymentStarted::class)[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getExposedContext()
    {
        /** @var DeploymentSuccessful[] $deploymentSuccessfulEvents */
        $deploymentSuccessfulEvents = $this->getEventsOfType(DeploymentSuccessful::class);
        if (count($deploymentSuccessfulEvents) == 0) {
            $services = new WildcardObject([
                'created' => false,
                'updated' => false,
                'deleted' => false,
            ]);
        } else {
            $componentStatuses = $deploymentSuccessfulEvents[0]->getDeployment()->getComponentStatuses();
            $services = new ArrayObject(array_map(function (ComponentStatus $status) {
                return json_decode(json_encode([
                    'created' => $status->isCreated(),
                    'updated' => $status->isUpdated(),
                    'deleted' => $status->isDeleted(),
                ]));
            }, $componentStatuses));
        }

        return new ArrayObject([
            'services' => $services,
        ]);
    }

    /**
     * @return DeployTaskConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return Client\PublicEndpoint[]
     */
    public function getPublicEndpoints() : array
    {
        return $this->publicEndpoints;
    }

    /**
     * @return Deployment|null
     */
    public function getStartedDeployment()
    {
        return $this->startedDeployment;
    }
}
