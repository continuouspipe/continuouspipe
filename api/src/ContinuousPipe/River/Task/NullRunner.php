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
}
