<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\Run\Command\StartRunCommand;
use ContinuousPipe\River\Task\Run\Event\RunEvent;
use ContinuousPipe\River\Task\Run\Event\RunFailed;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\Event\RunSuccessful;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\TideContext;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class RunTask extends EventDrivenTask
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
     * @param LoggerFactory $loggerFactory
     * @param MessageBus    $commandBus
     */
    public function __construct(LoggerFactory $loggerFactory, MessageBus $commandBus)
    {
        parent::__construct();

        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    public function start(TideContext $context)
    {
        $context = RunContext::createRunContext($context);

        $logger = $this->loggerFactory->from($context->getLog());
        $log = $logger->append(new Text(sprintf(
            'Running commands on image "%s"',
            $context->getImageName()
        )));

        $context->setRunnerLog($log);

        $this->commandBus->handle(new StartRunCommand($context->getTideUuid(), $context, $this->getTaskId($context)));
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideContext $tideContext, TideEvent $event)
    {
        if (!$this->isRelatedEvent($tideContext, $event)) {
            return;
        }

        parent::apply($tideContext, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return 0 < $this->numberOfEventsOfType(RunSuccessful::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return 0 < $this->numberOfEventsOfType(RunFailed::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return 0 === $this->numberOfEventsOfType(RunStarted::class);
    }

    /**
     * Returns true if the event is related to this
     *
     * @param TideContext $context
     * @param TideEvent $event
     * @return bool
     */
    private function isRelatedEvent(TideContext $context, TideEvent $event)
    {
        if (!$event instanceof RunEvent) {
            return false;
        }

        if ($event instanceof RunStarted) {
            var_dump($this->getTaskId($context), $event->getTaskId(), spl_object_hash($this));
            $acceptRunStarted = $this->getTaskId($context) == $event->getTaskId();
            var_dump($acceptRunStarted);
            return $acceptRunStarted;
        }

        $startedEvent = $this->getRunStartedEvent();

        return $startedEvent->getRunUuid()->equals($event->getRunUuid());
    }

    /**
     * @return RunStarted
     */
    private function getRunStartedEvent()
    {
        return $this->getEventsOfType(RunStarted::class)[0];
    }

    /**
     * @param TideContext $context
     *
     * @return int
     */
    private function getTaskId(TideContext $context)
    {
        return $context->get(TaskContext::KEY_TASK_ID);
    }
}
