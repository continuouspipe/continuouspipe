<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Tide;

class DelegatesToSupportingTaskRunner implements TaskRunner
{
    /**
     * @var array|TaskRunner[]
     */
    private $runners;

    /**
     * @param TaskRunner[] $runners
     */
    public function __construct(array $runners = [])
    {
        $this->runners = $runners;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        foreach ($this->runners as $runner) {
            if ($runner->supports($tide, $task)) {
                return $runner->run($tide, $task);
            }
        }

        throw new TaskRunnerException('Unable to run the task of type '.get_class($task), 0, null, $task);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task): bool
    {
        foreach ($this->runners as $runner) {
            if ($runner->supports($tide, $task)) {
                return true;
            }
        }

        return false;
    }
}
