<?php

namespace ContinuousPipe\River\Task;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface TaskFactory
{
    /**
     * @param TaskContext $taskContext
     * @param array       $configuration
     *
     * @return Task
     */
    public function create(TaskContext $taskContext, array $configuration);

    /**
     * @return NodeDefinition
     */
    public function getConfigTree();
}
