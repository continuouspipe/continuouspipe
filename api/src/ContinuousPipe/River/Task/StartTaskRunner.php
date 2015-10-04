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
        return $task->start();
    }
}
