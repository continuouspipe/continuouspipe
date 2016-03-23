<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Pipe\Client\ComponentStatus;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\TaskQueued;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
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
     * @param MessageBus              $commandBus
     * @param LoggerFactory           $loggerFactory
     * @param DeployContext           $context
     * @param DeployTaskConfiguration $configuration
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory, DeployContext $context, DeployTaskConfiguration $configuration)
    {
        parent::__construct($context);

        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->context = $context;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $logger = $this->loggerFactory->from($this->context->getLog());
        $log = $logger->child(new Text('Deploying environment'))->getLog();

        $this->context->setTaskLog($log);
        $this->newEvents[] = TaskQueued::fromContext($this->context);

        $this->commandBus->handle(new StartDeploymentCommand(
            $this->context->getTideUuid(),
            $this->context,
            $this->configuration
        ));
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
            return [];
        }

        $componentStatuses = $deploymentSuccessfulEvents[0]->getDeployment()->getComponentStatuses();
        $services = array_map(function (ComponentStatus $status) {
            return json_decode(json_encode([
                'created' => $status->isCreated(),
                'updated' => $status->isUpdated(),
                'deleted' => $status->isDeleted(),
            ]));
        }, $componentStatuses);

        $view = new \stdClass();
        $view->services = new ArrayObject($services);

        return $view;
    }

    /**
     * @return DeployTaskConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
