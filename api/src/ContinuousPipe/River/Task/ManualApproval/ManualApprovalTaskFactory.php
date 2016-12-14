<?php

namespace ContinuousPipe\River\Task\ManualApproval;

use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class ManualApprovalTaskFactory implements TaskFactory
{
    /**
     * @param TaskContext $taskContext
     * @param array $configuration
     *
     * @return Task
     */
    public function create(TaskContext $taskContext, array $configuration)
    {
        // TODO: Implement create() method.
    }

    /**
     * @return NodeDefinition
     */
    public function getConfigTree()
    {
        // TODO: Implement getConfigTree() method.
    }
}
