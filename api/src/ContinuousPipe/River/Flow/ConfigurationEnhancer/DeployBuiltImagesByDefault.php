<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DeployBuiltImagesByDefault implements ConfigurationEnhancer
{
    use ConfigurationEnhancer\Helper\TaskLocator;

    /**
     * {@inheritdoc}
     */
    public function enhance(Flow $flow, CodeReference $codeReference, array $configs)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $taskPathsAndTypes = $this->getTaskPathsAndType($configs);
        $enhancedConfig = $this->getEmptyConfiguration($configs);

        // Initialize paths else it will break the configuration order
        foreach ($taskPathsAndTypes as $path => $type) {
            $propertyAccessor->setValue($enhancedConfig, $path, []);
        }

        // Get the deploy paths only
        $deployPaths = array_keys(array_filter($taskPathsAndTypes, function ($type) {
            return $type == 'deploy';
        }));

        foreach ($deployPaths as $path) {
            $deployedServiceNames = $this->getServiceNames($configs, [$path]);

            foreach ($deployedServiceNames as $serviceName) {
                // Get the image tag values
                $imageTagPath = $path.'[services]['.$serviceName.'][specification][source][tag]';
                $values = $this->getValuesAtPath($configs, $imageTagPath);

                // If there's no value, we can't add the tag
                if (count($values) == 0 && $serviceTag = $this->getBuiltServiceValue($configs, $serviceName, 'tag')) {
                    $propertyAccessor->setValue($enhancedConfig, $imageTagPath, $serviceTag);
                }

                // Get the image name values
                $imageNamePath = $path.'[services]['.$serviceName.'][specification][source][image]';
                $values = $this->getValuesAtPath($configs, $imageNamePath);
                if (count($values) == 0 && $serviceImage = $this->getBuiltServiceValue($configs, $serviceName, 'image')) {
                    $propertyAccessor->setValue($enhancedConfig, $imageNamePath, $serviceImage);
                }
            }
        }

        array_unshift($configs, $enhancedConfig);

        return $configs;
    }
}
