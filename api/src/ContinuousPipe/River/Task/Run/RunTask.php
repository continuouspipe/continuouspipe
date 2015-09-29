<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\ContextKeyNotFound;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\Run\Command\StartRunCommand;
use ContinuousPipe\River\Task\Run\Event\RunFailed;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\Event\RunSuccessful;
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
     * @var RunContext
     */
    private $context;

    /**
     * @param LoggerFactory $loggerFactory
     * @param MessageBus    $commandBus
     * @param RunContext    $context
     */
    public function __construct(LoggerFactory $loggerFactory, MessageBus $commandBus, RunContext $context)
    {
        parent::__construct($context);

        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $logger = $this->loggerFactory->from($this->context->getLog());
        $log = $logger->append(new Text($this->getLogText()));

        $this->context->setRunnerLog($log);

        $this->commandBus->handle(new StartRunCommand(
            $this->context->getTideUuid(),
            $this->context,
            $this->context->getTaskId()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function accept(TideEvent $event)
    {
        if ($event instanceof RunFailed || $event instanceof RunSuccessful) {
            if (!$this->isStarted()) {
                return false;
            }

            return $this->getRunStartedEvent()->getRunUuid()->equals($event->getRunUuid());
        }

        return parent::accept($event);
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
     * @return bool
     */
    private function isStarted()
    {
        return 0 < $this->numberOfEventsOfType(RunStarted::class);
    }

    /**
     * @return RunStarted
     */
    private function getRunStartedEvent()
    {
        return $this->getEventsOfType(RunStarted::class)[0];
    }

    /**
     * @return string
     */
    private function getLogText()
    {
        try {
            return sprintf(
                'Running commands on image "%s"',
                $this->context->getImageName()
            );
        } catch (ContextKeyNotFound $e) {
            return sprintf(
                'Running commands on service "%s"',
                $this->context->getServiceName()
            );
        }
    }
}
