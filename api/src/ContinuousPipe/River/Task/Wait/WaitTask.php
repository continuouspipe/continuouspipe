<?php

namespace ContinuousPipe\River\Task\Wait;

use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\Wait\Event\WaitFailed;
use ContinuousPipe\River\Task\Wait\Event\WaitStarted;
use ContinuousPipe\River\Task\Wait\Event\WaitSuccessful;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class WaitTask extends EventDrivenTask
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var TaskContext
     */
    private $context;

    /**
     * @var WaitTaskConfiguration
     */
    private $configuration;

    /**
     * @var WaitStarted|null
     */
    private $startedEvent;

    /**
     * @param EventCollection       $eventCollection
     * @param LoggerFactory         $loggerFactory
     * @param TaskContext           $context
     * @param WaitTaskConfiguration $configuration
     */
    public function __construct(EventCollection $eventCollection, LoggerFactory $loggerFactory, TaskContext $context, WaitTaskConfiguration $configuration)
    {
        parent::__construct($context, $eventCollection);

        $this->loggerFactory = $loggerFactory;
        $this->context = $context;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $status = $this->configuration->getStatus();
        $logger = $this->loggerFactory->from($this->context->getLog());
        $log = $logger->child(new Text(sprintf(
            'Waiting for status "%s" to be "%s" (%s)',
            $status->getContext(),
            $status->getState(),
            $this->getLabel()
        )))->getLog();

        $this->events->raiseAndApply(new WaitStarted(
            $this->context->getTideUuid(),
            $log,
            $this->context->getTaskId()
        ));
    }

    public function statusUpdated(StatusUpdated $event)
    {
        $gitHubEvent = $event->getGitHubStatusEvent();
        $waitingStatus = $this->configuration->getStatus();

        if ($gitHubEvent->getContext() != $waitingStatus->getContext()) {
            return;
        }

        if ($gitHubEvent->getState() == $waitingStatus->getState()) {
            $this->events->raiseAndApply(new WaitSuccessful($this->startedEvent));
        } else {
            $this->events->raiseAndApply(new WaitFailed($this->startedEvent));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideEvent $event)
    {
        if ($event instanceof WaitStarted) {
            $this->startedEvent = $event;
        }

        parent::apply($event);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return 0 < $this->numberOfEventsOfType(WaitSuccessful::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return 0 < $this->numberOfEventsOfType(WaitFailed::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return 0 === $this->numberOfEventsOfType(WaitStarted::class);
    }
}
