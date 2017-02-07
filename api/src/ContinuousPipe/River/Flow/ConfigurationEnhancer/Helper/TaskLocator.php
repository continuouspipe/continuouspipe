<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer\Helper;

use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
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
            if (!is_array($config)) {
                continue;
            }

            foreach ($this->findPathsInConfig($config) as $path => $taskName) {
                $paths[$path] = $taskName;
            }

            if (array_key_exists('pipelines', $config)) {
                foreach ($config['pipelines'] as $key => $pipeline) {
                    if (!is_array($pipeline)) {
                        continue;
                    }

                    foreach ($this->findPathsInConfig($pipeline) as $path => $taskName) {
                        $paths['[pipelines]['.$key.']'.$path] = $taskName;
                    }
                }
            }
        }

        return $paths;
    }

    /**
     * @param array $configs
     *
     * @return array
     */
    private function getEmptyConfiguration(array $configs)
    {
        $configuration = [
            'tasks' => [],
        ];

        foreach ($configs as $config) {
            if (!array_key_exists('tasks', $config)) {
                continue;
            }

            foreach ($config['tasks'] as $name => $task) {
                $configuration['tasks'][$name] = [];
            }
        }

        return $configuration;
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

    /**
     * @param array  $configs
     * @param string $serviceName
     *
     * @return string|null
     */
    private function getBuiltServiceValue(array $configs, $serviceName, $key)
    {
        $buildPaths = array_keys(array_filter($this->getTaskPathsAndType($configs), function ($type) {
            return $type == 'build';
        }));

        $paths = [];
        foreach ($buildPaths as $path) {
            $paths[] = $path . '[services][' . $serviceName . '][' . $key . ']';
            $paths = array_merge($paths, $this->expandSelector($configs, $path . '[services][' . $serviceName . '][steps][*][' . $key . ']'));
        }

        $values = [];
        foreach ($paths as $path) {
            $values = array_merge($values, $this->getValuesAtPath($configs, $path));
        }

        if (0 == count($values)) {
            return;
        }

        return $values[count($values) - 1];
    }

    /**
     * @param array $config
     *
     * @return string[]
     */
    private function findPathsInConfig(array $config)
    {
        if (!array_key_exists('tasks', $config)) {
            return [];
        }

        $paths = [];
        foreach ($config['tasks'] as $key => $task) {
            if (!is_array($task)) {
                continue;
            }

            foreach ($task as $taskName => $taskConfiguration) {
                if ($this->ignoreTaskKey($taskName)) {
                    continue;
                }

                $path = '[tasks]['.$key.']['.$taskName.']';
                if (!array_key_exists($path, $paths)) {
                    $paths[$path] = $taskName;
                }
            }
        }

        return $paths;
    }


    /**
     * @param array  $configs
     * @param string $selector
     *
     * @return array
     */
    private function expandSelector(array $configs, $selector)
    {
        $wildcardedSelector = explode('[*]', $selector);
        if (count($wildcardedSelector) == 1) {
            return [$selector];
        }

        $firstSelector = array_shift($wildcardedSelector);
        $selectorEnd = implode('[*]', $wildcardedSelector);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $paths = [];

        foreach ($configs as $config) {
            try {
                $value = $propertyAccessor->getValue($config, $firstSelector);
            } catch (UnexpectedTypeException $e) {
                continue;
            }

            if (!is_array($value)) {
                continue;
            }

            foreach ($value as $key => $v) {
                $paths = array_merge($paths, $this->expandSelector($configs, $firstSelector.'['.$key.']'.$selectorEnd));
            }
        }

        return array_unique($paths);
    }

    /**
     * @param string $taskName
     *
     * @return bool
     */
    private function ignoreTaskKey(string $taskName): bool
    {
        return $taskName == 'imports';
    }
}
