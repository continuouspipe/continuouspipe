<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Pipeline\TideGenerationException;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface TaskFactory
{
    /**
     * @param EventCollection $events
     * @param TaskContext     $taskContext
     * @param array           $configuration
     *
     * @throws TideGenerationException
     *
     * @return Task
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration);

    /**
     * @return NodeDefinition
     */
    public function getConfigTree();
}
