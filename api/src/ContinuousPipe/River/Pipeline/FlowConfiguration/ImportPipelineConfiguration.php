<?php

namespace ContinuousPipe\River\Pipeline\FlowConfiguration;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideConfigurationFactory;

class ImportPipelineConfiguration implements TideConfigurationFactory
{
    private $decoratedFactory;

    public function __construct(TideConfigurationFactory $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    /**
     * {@inheritdoc.
     */
    public function getConfiguration(FlatFlow $flow, CodeReference $codeReference)
    {
        $configuration = $this->decoratedFactory->getConfiguration($flow, $codeReference);

        foreach ($configuration['pipelines'] as &$pipeline) {
            foreach ($pipeline['tasks'] as &$task) {
                if (!isset($task['imports'])) {
                    continue;
                }

                if (!array_key_exists($task['imports'], $configuration['tasks'])) {
                    throw new TideConfigurationException(sprintf(
                        'Unable to import task "%s": The task do not exists',
                        $task['imports']
                    ));
                }

                $task = array_merge($configuration['tasks'][$task['imports']], $task);

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
        }

        return $configuration;
    }
}
