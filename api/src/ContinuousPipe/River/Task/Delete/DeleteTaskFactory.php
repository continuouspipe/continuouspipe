<?php

namespace ContinuousPipe\River\Task\Delete;

use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskCreated;
use ContinuousPipe\River\Task\TaskFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DeleteTaskFactory implements TaskFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration)
    {
        return new DeleteTask(
            $events,
            [
                new TaskCreated(
                    $taskContext->getTideUuid(),
                    $taskContext->getTaskId(),
                    new \DateTime(),
                    $configuration
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
        $node = $builder->root('delete');
        $node
            ->children()
                ->scalarNode('cluster')->isRequired()->end()
                ->arrayNode('environment')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
