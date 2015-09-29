<?php

namespace ContinuousPipe\River\Task\Run\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GetImageNameFromBuiltServices implements ConfigurationEnhancer
{
    use ConfigurationEnhancer\Helper\TaskLocator;

    /**
     * {@inheritdoc}
     */
    public function enhance(Flow $flow, CodeReference $codeReference, array $configs)
    {
        $runTaskPaths = array_keys(array_filter($this->getTaskPathsAndType($configs), function ($type) {
            return $type == 'run';
        }));

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $enhancedConfiguration = [];

        foreach ($runTaskPaths as $path) {
            $values = $this->getValuesAtPath($configs, $path);
            $imageNameConfigurations = array_filter($values, function ($value) {
                return is_array($value) && isset($value['image']['name']);
            });

            if (0 == count($imageNameConfigurations)) {
                $fromServiceConfigurations = array_values(array_filter($values, function ($value) {
                    return is_array($value) && isset($value['image']['from_service']);
                }));

                if (count($fromServiceConfigurations) > 0) {
                    $service = $fromServiceConfigurations[count($fromServiceConfigurations) - 1]['image']['from_service'];

                    if ($image = $this->getBuiltServiceValue($configs, $service, 'image')) {
                        if ($tag = $this->getBuiltServiceValue($configs, $service, 'tag')) {
                            $image .= ':'.$tag;
                        }

                        $propertyAccessor->setValue($enhancedConfiguration, $path.'[image][name]', $image);
                    }
                }
            }
        }

        $configs[] = $enhancedConfiguration;

        return $configs;
    }
}
