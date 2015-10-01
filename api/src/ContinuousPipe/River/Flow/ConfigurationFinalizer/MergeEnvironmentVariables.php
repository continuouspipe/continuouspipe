<?php

namespace ContinuousPipe\River\Flow\ConfigurationFinalizer;

use ContinuousPipe\River\Flow\ConfigurationFinalizer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MergeEnvironmentVariables implements ConfigurationFinalizer
{
    /**
     * @var array
     */
    private $paths;

    /**
     * @param array $paths
     */
    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function finalize(array $configuration)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $paths = $this->resolvePaths($configuration, $this->paths);

        foreach ($paths as $path) {
            if (!$propertyAccessor->isReadable($configuration, $path)) {
                continue;
            }

            $variables = $propertyAccessor->getValue($configuration, $path);
            if (!is_array($variables)) {
                continue;
            }

            $propertyAccessor->setValue($configuration, $path, $this->mergeEnvironmentVariables($variables));
        }

        return $configuration;
    }

    /**
     * @param array $variables
     *
     * @return array
     */
    private function mergeEnvironmentVariables(array $variables)
    {
        $variableReferences = [];

        foreach ($variables as $index => $variable) {
            $name = $variable['name'];

            if (array_key_exists($name, $variableReferences)) {
                unset($variables[$variableReferences[$name]]);
            }

            $variableReferences[$name] = $index;
        }

        return array_values($variables);
    }

    /**
     * @param array $configuration
     * @param array $paths
     *
     * @return array
     */
    private function resolvePaths(array $configuration, array $paths)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $foundPaths = [];

        foreach ($paths as $path) {
            $wildCardExploded = explode('[*]', $path);
            if (count($wildCardExploded) == 1) {
                $foundPaths[] = $path;

                continue;
            }

            $currentPath = array_shift($wildCardExploded);
            $value = $propertyAccessor->getValue($configuration, $currentPath);
            if (!is_array($value)) {
                continue;
            }

            $subPaths = [];
            foreach ($value as $k => $v) {
                $subPaths[] = $currentPath.'['.$k.']'.implode('[*]', $wildCardExploded);
            }

            $foundPaths = array_merge($foundPaths, $this->resolvePaths($configuration, $subPaths));
        }

        return $foundPaths;
    }
}
