<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class TaskListAsUnnamedArrayEnhancer implements ConfigurationEnhancer
{
    /**
     * {@inheritdoc}
     */
    public function enhance(FlatFlow $flow, CodeReference $codeReference, array $configs)
    {
        foreach ($configs as &$config) {
            if (!$config || !array_key_exists('tasks', $config)) {
                continue;
            }

            $nonStringKeys = array_filter(array_keys($config['tasks']), function ($key) {
                return !is_string($key);
            });

            foreach ($nonStringKeys as $key) {
                $config['tasks']['task'.$key] = $config['tasks'][$key];

                unset($config['tasks'][$key]);
            }
        }

        return $configs;
    }
}
