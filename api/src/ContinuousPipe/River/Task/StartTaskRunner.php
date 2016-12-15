<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Tide;

class StartTaskRunner implements TaskRunner
{
    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        if (method_exists($task, 'start')) {
            return $task->start();
        }

        throw new TaskRunnerException('Unable to find a way to start the task', 0, null, $task);
    }
}
