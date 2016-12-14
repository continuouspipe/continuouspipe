<?php

namespace ContinuousPipe\River\Task\Wait;

use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\Task;
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
     * @param LoggerFactory         $loggerFactory
     * @param TaskContext           $context
     * @param WaitTaskConfiguration $configuration
     */
    public function __construct(LoggerFactory $loggerFactory, TaskContext $context, WaitTaskConfiguration $configuration)
    {
        parent::__construct($context);

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
            'Waiting status "%s" to be "%s"',
            $status->getContext(),
            $status->getState()
        )))->getLog();

        $this->newEvents[] = new WaitStarted(
            $this->context->getTideUuid(),
            $log,
            $this->context->getTaskId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function accept(TideEvent $event)
    {
        if ($event instanceof StatusUpdated) {
            return true;
        }

        return parent::accept($event);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideEvent $event)
    {
        if ($event instanceof StatusUpdated && $this->getStatus() == Task::STATUS_RUNNING) {
            $this->applyStatusUpdated($event);
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

    /**
     * @return WaitStarted
     */
    private function getWaitStartedEvent()
    {
        return $this->getEventsOfType(WaitStarted::class)[0];
    }

    /**
     * @param StatusUpdated $event
     */
    private function applyStatusUpdated(StatusUpdated $event)
    {
        $gitHubEvent = $event->getGitHubStatusEvent();
        $waitingStatus = $this->configuration->getStatus();

        if ($gitHubEvent->getContext() != $waitingStatus->getContext()) {
            return;
        }

        $waitStartedEvent = $this->getWaitStartedEvent();
        if ($gitHubEvent->getState() == $waitingStatus->getState()) {
            $this->newEvents[] = new WaitSuccessful($waitStartedEvent);
        } else {
            $this->newEvents[] = new WaitFailed($waitStartedEvent);
        }
    }
}
