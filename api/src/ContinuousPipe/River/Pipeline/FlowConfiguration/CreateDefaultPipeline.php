<?php

namespace ContinuousPipe\River\Pipeline\FlowConfiguration;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\ConfigurationFinalizer;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

final class CreateDefaultPipeline implements ConfigurationFinalizer
{
    /**
     * {@inheritdoc}
     */
    public function finalize(FlatFlow $flow, CodeReference $codeReference, array $configuration)
    {
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
