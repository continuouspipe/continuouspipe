<?php

namespace ContinuousPipe\River\Flow\ConfigurationFinalizer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\ConfigurationFinalizer;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\TideConfigurationException;

class OrderTasksByKey implements ConfigurationFinalizer
{
    /**
     * {@inheritdoc}
     */
    public function finalize(FlatFlow $flow, CodeReference $codeReference, array $configuration)
    {
        foreach ($configuration['pipelines'] as &$pipeline) {
            $pipeline = $this->sortTasks($pipeline);
        }

        $configuration = $this->sortTasks($configuration);

        return $configuration;
    }

    private function sortTasks(array $configuration)
    {
        if (!array_key_exists('tasks', $configuration)) {
            return $configuration;
        }

        $allNumericKey = array_reduce(array_keys($configuration['tasks']), function(bool $allNumericKey, $key) {
            return $allNumericKey && is_numeric($key);
        }, true);

        if ($allNumericKey) {
            ksort($configuration['tasks']);
        }

        return $configuration;
    }
}
