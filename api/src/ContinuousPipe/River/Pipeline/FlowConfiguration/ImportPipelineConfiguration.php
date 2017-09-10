<?php

namespace ContinuousPipe\River\Pipeline\FlowConfiguration;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\ConfigurationDefinition;
use ContinuousPipe\River\Flow\ConfigurationFinalizer;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Task\TaskFactoryRegistry;
use ContinuousPipe\River\TideConfigurationException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ImportPipelineConfiguration implements ConfigurationFinalizer
{
    private $taskFactoryRegistry;

    public function __construct(TaskFactoryRegistry $taskFactoryRegistry)
    {
        $this->taskFactoryRegistry = $taskFactoryRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function finalize(FlatFlow $flow, CodeReference $codeReference, array $configuration)
    {
        if (!isset($configuration['tasks'])) {
            return $configuration;
        }

        $tasksConfigurationDefinition = new ConfigurationDefinition($this->taskFactoryRegistry);

        $builder = new TreeBuilder();
        $node = $builder->root('task');
        $tasksConfigurationDefinition->setupTasksPrototype($node);

        foreach ($configuration['pipelines'] as &$pipeline) {
            if (!isset($pipeline['tasks'])) {
                continue;
            }

            foreach ($pipeline['tasks'] as &$task) {
                if (!isset($task['imports'])) {
                    continue;
                }

                $taskName = $task['imports'];
                if (!array_key_exists($taskName, $configuration['tasks'])) {
                    throw new TideConfigurationException(sprintf(
                        'Unable to import task "%s": The task does not exist',
                        $task['imports']
                    ));
                }

                $tree = $builder->buildTree();
                $task = $tree->merge($configuration['tasks'][$taskName], $task);

                if (!array_key_exists('name', $task)) {
                    $task['identifier'] = $task['imports'];
                }

                unset($task['imports']);
            }

            if (!isset($pipeline['notifications']) && isset($configuration['notifications'])) {
                $pipeline['notifications'] = $configuration['notifications'];
            }

            if (!isset($pipeline['filter']) && isset($configuration['filter'])) {
                $pipeline['filter'] = $configuration['filter'];
            }

            if (isset($configuration['variables'])) {
                if (!isset($pipeline['variables'])) {
                    $pipeline['variables'] = [];
                }

                $pipeline['variables'] = array_merge($configuration['variables'], $pipeline['variables']);
            }
        }

        return $configuration;
    }
}
