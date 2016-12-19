<?php

namespace ContinuousPipe\River\Pipeline\FlowConfiguration;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\TideConfigurationFactory;

final class CreateDefaultPipeline implements TideConfigurationFactory
{
    private $decoratedFactory;

    public function __construct(TideConfigurationFactory $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(FlatFlow $flow, CodeReference $codeReference)
    {
        $configuration = $this->decoratedFactory->getConfiguration($flow, $codeReference);

        if (empty($configuration['pipelines'])) {
            $tasks = [];
            $hasUniqueNames = !has_dupes(array_keys($configuration['tasks']));

            foreach ($configuration['tasks'] as $name => $taskConfiguration) {
                $tasks[$hasUniqueNames ? $name : count($tasks)] = [
                    'imports' => $name,
                ];
            }

            $configuration['pipelines'] = [
                [
                    'name' => 'Default pipeline',
                    'tasks' => $tasks,
                ],
            ];
        }

        return $configuration;
    }
}

function has_dupes($array)
{
    $foundValues = [];

    foreach ($array as $val) {
        if (!isset($foundValues[$val])) {
            $foundValues[$val] = 0;
        }

        if (++$foundValues[$val] > 1) {
            return true;
        }
    }

    return false;
}
