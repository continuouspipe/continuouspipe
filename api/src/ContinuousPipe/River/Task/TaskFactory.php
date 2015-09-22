<?php

namespace ContinuousPipe\River\Task;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface TaskFactory
{
    /**
     * @param TaskContext $taskContext
     *
     * @return Task
     */
    public function create(TaskContext $taskContext);

    /**
     * @return NodeDefinition
     */
    public function getConfigTree();
}
