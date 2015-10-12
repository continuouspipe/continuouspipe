<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\Run\Command\StartRunCommand;
use ContinuousPipe\River\Task\Run\Event\RunFailed;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\Event\RunSuccessful;
use ContinuousPipe\River\Task\TaskQueued;
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
     * @var RunTaskConfiguration
     */
    private $configuration;

    /**
     * @param LoggerFactory        $loggerFactory
     * @param MessageBus           $commandBus
     * @param RunContext           $context
     * @param RunTaskConfiguration $configuration
     */
    public function __construct(LoggerFactory $loggerFactory, MessageBus $commandBus, RunContext $context, RunTaskConfiguration $configuration)
    {
        parent::__construct($context);

        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->context = $context;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $logger = $this->loggerFactory->from($this->context->getLog());
        $log = $logger->append(new Text(sprintf(
            'Running "%s" on the image "%s"',
            implode(' ', $this->configuration->getCommands()),
            $this->configuration->getImage()
        )));

        $this->context->setTaskLog($log);
        $this->newEvents[] = TaskQueued::fromContext($this->context);

        $this->commandBus->handle(new StartRunCommand(
            $this->context->getTideUuid(),
            $this->context,
            $this->configuration
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
}
