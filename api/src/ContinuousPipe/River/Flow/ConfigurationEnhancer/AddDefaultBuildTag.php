<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AddDefaultBuildTag implements ConfigurationEnhancer
{
    use ConfigurationEnhancer\Helper\TaskLocator;

    /**
     * {@inheritdoc}
     */
    public function enhance(Flow $flow, CodeReference $codeReference, array $configs)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $taskPathsAndTypes = $this->getTaskPathsAndType($configs);
        $enhancedConfig = [];

        // Initialize paths else it will break the configuration order
        foreach ($taskPathsAndTypes as $path => $type) {
            $propertyAccessor->setValue($enhancedConfig, $path, []);
        }

        // Get all the build task paths
        $buildPaths = array_filter($taskPathsAndTypes, function ($type) {
            return $type == 'build';
        });

        $builtServiceNames = $this->getServiceNames($configs, array_keys($buildPaths));
        foreach ($buildPaths as $path => $taskType) {
            foreach ($builtServiceNames as $serviceName) {
                // Get the image name values
                $imageNamePath = $path.'[services]['.$serviceName.'][image]';
                $imageTagPath = $path.'[services]['.$serviceName.'][tag]';

                $values = $this->getValuesAtPath($configs, $imageNamePath);

                // If there's no value, we can't add the tag
                if (count($values) == 0) {
                    continue;
                }

                // If there's no image name with tag name, then add one
                $tags = $this->getValuesAtPath($configs, $imageTagPath);
                if (count($tags) == 0) {
                    $propertyAccessor->setValue($enhancedConfig, $imageTagPath, $codeReference->getBranch());
                }
            }
        }

        array_unshift($configs, $enhancedConfig);

        return $configs;
    }
}
