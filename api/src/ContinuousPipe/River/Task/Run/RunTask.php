<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\Run\Command\StartRunCommand;
use ContinuousPipe\River\Task\Run\Event\RunFailed;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\Event\RunSuccessful;
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

        $this->commandBus->handle(new StartRunCommand($context->getTideUuid(), $context));
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
}
