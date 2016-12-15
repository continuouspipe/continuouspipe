<?php

namespace ContinuousPipe\River\Task\ManualApproval;

use ContinuousPipe\River\Task\ManualApproval\Event\TaskCreated;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ManualApprovalTaskFactory implements TaskFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(TaskContext $taskContext, array $configuration)
    {
        return ManualApprovalTask::fromEvents([
            new TaskCreated(
                $taskContext->getTideUuid(),
                $taskContext->getTaskId()
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('manual_approval');

        $node
            ->children()

            ->end()
        ;

        return $node;
    }
}
