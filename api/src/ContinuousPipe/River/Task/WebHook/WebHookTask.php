<?php

namespace ContinuousPipe\River\Task\WebHook;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskQueued;
use ContinuousPipe\River\Task\WebHook\Command\SendWebHook;
use ContinuousPipe\River\Task\WebHook\Event\WebHookFailed;
use ContinuousPipe\River\Task\WebHook\Event\WebHookSent;
use ContinuousPipe\River\WebHook\WebHook;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class WebHookTask extends EventDrivenTask
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var PublicEndpoint[]
     */
    private $publicEndpoints = [];

    /**
     * @param TaskContext   $context
     * @param LoggerFactory $loggerFactory
     * @param MessageBus    $commandBus
     * @param array         $configuration
     */
    public function __construct(TaskContext $context, LoggerFactory $loggerFactory, MessageBus $commandBus, array $configuration)
    {
        parent::__construct($context);

        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $context = $this->getContext();
        $logger = $this->loggerFactory->from($context->getLog());
        $log = $logger->child(new Text('Sending the web-hook'))->getLog();

        $webHook = new WebHook(
            Uuid::uuid1(),
            $this->configuration['url'],
            $context->getCodeReference(),
            $this->publicEndpoints
        );

        $this->commandBus->handle(new SendWebHook($context->getTideUuid(), $context->getTaskId(), $log->getId(), $webHook));

        $this->newEvents[] = new TaskQueued($context->getTideUuid(), $context->getTaskId(), $log);
    }

    /**
     * {@inheritdoc}
     */
    public function accept(TideEvent $event)
    {
        if ($event instanceof DeploymentSuccessful) {
            return true;
        }

        return parent::accept($event);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideEvent $event)
    {
        if ($event instanceof DeploymentSuccessful) {
            $this->applyDeploymentSuccessful($event);
        }

        parent::apply($event);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return $this->numberOfEventsOfType(WebHookSent::class) >= 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return $this->numberOfEventsOfType(WebHookFailed::class) > 0;
    }

    /**
     * @param DeploymentSuccessful $event
     */
    private function applyDeploymentSuccessful(DeploymentSuccessful $event)
    {
        $this->publicEndpoints = array_merge($this->publicEndpoints, $event->getDeployment()->getPublicEndpoints());
    }
}
