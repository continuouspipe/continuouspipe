<?php

namespace ContinuousPipe\River\Task\ManualApproval;

use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\TaskCreated;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ManualApprovalTaskFactory implements TaskFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration)
    {
        return new ManualApprovalTask(
            $events,
            [
                new TaskCreated(
                    $taskContext->getTideUuid(),
                    $taskContext->getTaskId()
                ),
            ]
        );
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
