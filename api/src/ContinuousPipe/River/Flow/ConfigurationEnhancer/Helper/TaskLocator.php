<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer\Helper;

use Symfony\Component\PropertyAccess\PropertyAccess;

trait TaskLocator
{
    /**
     * @param array $configs
     *
     * @return array
     */
    private function getTaskPathsAndType(array $configs)
    {
        $paths = [];

        foreach ($configs as $config) {
            if (!array_key_exists('tasks', $config)) {
                continue;
            }

            foreach ($config['tasks'] as $key => $task) {
                foreach ($task as $taskName => $taskConfiguration) {
                    $path = '[tasks]['.$key.']['.$taskName.']';
                    $paths[$path] = $taskName;
                }
            }
        }

        return array_unique($paths);
    }

    /**
     * @param array  $configs
     * @param string $path
     *
     * @return array
     */
    private function getValuesAtPath(array $configs, $path)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $values = [];

        foreach ($configs as $config) {
            if ($propertyAccessor->isReadable($config, $path)) {
                $value = $propertyAccessor->getValue($config, $path);

                if (!empty($value)) {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * @param array $configs
     * @param array $paths
     *
     * @return array
     */
    private function getServiceNames(array $configs, array $paths)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $serviceNames = [];

        foreach ($configs as $config) {
            foreach ($paths as $path) {
                if (!$propertyAccessor->isReadable($config, $path)) {
                    continue;
                }

                $configuration = $propertyAccessor->getValue($config, $path);
                if (!is_array($configuration)) {
                    continue;
                }

                if (!array_key_exists('services', $configuration)) {
                    continue;
                }

                $serviceNames = array_merge($serviceNames, array_keys($configuration['services']));
            }
        }

        return array_unique($serviceNames);
    }
}
