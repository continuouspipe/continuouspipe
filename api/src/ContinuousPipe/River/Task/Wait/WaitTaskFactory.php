<?php

namespace ContinuousPipe\River\Task\Wait;

use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use ContinuousPipe\River\Task\Wait\Configuration\Status;
use LogStream\LoggerFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class WaitTaskFactory implements TaskFactory
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration)
    {
        return new WaitTask(
            $events,
            $this->loggerFactory,
            $taskContext,
            $this->createConfiguration($configuration)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('wait');

        $node
            ->children()
                ->arrayNode('status')
                    ->isRequired()
                    ->children()
                        ->scalarNode('context')->isRequired()->end()
                        ->scalarNode('state')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @param array $configuration
     *
     * @return WaitTaskConfiguration
     */
    private function createConfiguration(array $configuration)
    {
        return new WaitTaskConfiguration(
            new Status(
                $configuration['status']['context'],
                $configuration['status']['state']
            )
        );
    }
}
