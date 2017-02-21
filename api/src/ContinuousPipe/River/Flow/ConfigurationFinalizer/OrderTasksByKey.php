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

        if (array_key_exists('tasks', $configuration)) {
            $configuration = $this->sortTasks($configuration);
        }

        return $configuration;
    }

    private function sortTasks(array $configuration)
    {
        ksort($configuration['tasks']);

        return $configuration;
    }
}
