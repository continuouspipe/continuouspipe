<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Tide;

class NullRunner implements TaskRunner
{
    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task): bool
    {
        return true;
    }
}
