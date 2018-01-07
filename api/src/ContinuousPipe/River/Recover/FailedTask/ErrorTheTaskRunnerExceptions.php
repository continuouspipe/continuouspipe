<?php

namespace ContinuousPipe\River\Recover\FailedTask;

use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Tide;
use Psr\Log\LoggerInterface;

class ErrorTheTaskRunnerExceptions implements TaskRunner
{
    /**
     * @var TaskRunner
     */
    private $decoratedRunner;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TaskRunner      $decoratedRunner
     * @param LoggerInterface $logger
     */
    public function __construct(TaskRunner $decoratedRunner, LoggerInterface $logger)
    {
        $this->decoratedRunner = $decoratedRunner;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        try {
            return $this->decoratedRunner->run($tide, $task);
        } catch (TaskRunnerException $e) {
            $this->logger->error('Unable to start a task', [
                'exception' => $e,
                'tide_uuid' => (string) $tide->getUuid(),
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task): bool
    {
        return $this->decoratedRunner->supports($tide, $task);
    }
}
