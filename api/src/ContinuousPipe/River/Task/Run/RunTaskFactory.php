<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;

class RunTaskFactory implements TaskFactory
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
        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    public function create(TaskContext $taskContext)
    {
        return new RunTask($this->loggerFactory, $this->commandBus, RunContext::createRunContext($taskContext));
    }
}
