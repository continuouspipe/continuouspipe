<?php

namespace ContinuousPipe\River\Task\WebHook;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskQueued;
use ContinuousPipe\River\Task\WebHook\Event\WebHookFailed;
use ContinuousPipe\River\Task\WebHook\Event\WebHookSent;
use ContinuousPipe\River\WebHook\WebHook;
use ContinuousPipe\River\WebHook\WebHookClient;
use ContinuousPipe\River\WebHook\WebHookException;
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
     * @param EventCollection $events
     * @param TaskContext $context
     * @param LoggerFactory $loggerFactory
     * @param MessageBus $commandBus
     * @param array $configuration
     */
    public function __construct(EventCollection $events, TaskContext $context, LoggerFactory $loggerFactory, MessageBus $commandBus, array $configuration)
    {
        parent::__construct($context, $events);

        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->configuration = $configuration;
    }

    public function send(WebHookClient $webHookClient)
    {
        $context = $this->getContext();
        $logger = $this->loggerFactory->fromId($this->getIdentifier());
        $log = $logger->child(new Text('Sending the web-hook'))->getLog();

        $webHook = new WebHook(
            Uuid::uuid1(),
            $this->configuration['url'],
            $context->getCodeReference(),
            $this->publicEndpoints
        );

        $this->events->raiseAndApply(new TaskQueued($context->getTideUuid(), $context->getTaskId(), $log));

        try {
            $webHookClient->send($webHook);

            $this->events->raiseAndApply(new WebHookSent(
                $context->getTideUuid(),
                $this->getIdentifier(),
                $webHook
            ));

            $logger->child(new Text(
                sprintf(
                    'Running web-hook task "%s": webhook sent to "%s"',
                    $this->getIdentifier(),
                    $webHook->getUrl()
                )
            ));
        } catch (WebHookException $e) {
            $this->events->raiseAndApply(new WebHookFailed(
                $context->getTideUuid(),
                $this->getIdentifier(),
                $webHook,
                $e->getMessage()
            ));

            $logger->child(new Text(sprintf(
                'Sending webhook to "%s" failed: %s',
                $webHook->getUrl(),
                $e->getMessage()
            )));
        }
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
            $this->publicEndpoints = array_merge($this->publicEndpoints, $event->getDeployment()->getPublicEndpoints());
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
}
