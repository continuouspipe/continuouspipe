<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AddDefaultValues implements ConfigurationEnhancer
{
    use ConfigurationEnhancer\Helper\TaskLocator;

    /**
     * {@inheritdoc}
     */
    public function enhance(Flow $flow, CodeReference $codeReference, array $configs)
    {
        $defaults = $this->getDefaultValues($configs);
        $defaultConfiguration = [];

        if (!empty($defaults)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $deployAndRunTaskPaths = $this->getTaskPathsOfType($configs, ['deploy', 'run']);

            foreach ($deployAndRunTaskPaths as $path) {
                $propertyAccessor->setValue($defaultConfiguration, $path, $defaults);
            }

            array_unshift($configs, $defaultConfiguration);
        }

        return $configs;
    }

    /**
     * @param array $configs
     *
     * @return array
     */
    private function getDefaultValues(array $configs)
    {
        $defaults = [];

        foreach ($this->getValuesAtPath($configs, '[defaults]') as $value) {
            $defaults = array_merge_recursive($defaults, $value);
        }

        return $defaults;
    }

    /**
     * @param array $configs
     * @param array $types
     *
     * @return array
     */
    private function getTaskPathsOfType(array $configs, array $types)
    {
        $paths = [];

        foreach ($this->getTaskPathsAndType($configs) as $path => $type) {
            if (in_array($type, $types)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
