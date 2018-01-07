<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GetImageNameFromBuiltServices implements ConfigurationEnhancer
{
    use ConfigurationEnhancer\Helper\TaskLocator;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(FlatFlow $flow, CodeReference $codeReference, array $configs)
    {
        $paths = $this->expandSelector($configs, $this->configuration['selector']);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $enhancedConfiguration = $this->getEmptyConfiguration($configs);

        foreach ($paths as $path) {
            $values = $this->getValuesAtPath($configs, $path);
            $fromServiceConfigurations = array_values($this->getConfigurations($values, $this->configuration['servicePath']));
            if (count($fromServiceConfigurations) === 0) {
                continue;
            }

            $service = $propertyAccessor->getValue($fromServiceConfigurations[count($fromServiceConfigurations) - 1], $this->configuration['servicePath']);
            if ($image = $this->getBuiltServiceValue($configs, $service, 'image')) {
                if ($tag = $this->getBuiltServiceValue($configs, $service, 'tag')) {
                    if (array_key_exists('tagPath', $this->configuration)) {
                        $propertyAccessor->setValue($enhancedConfiguration, $path.$this->configuration['tagPath'], $tag);
                    } else {
                        $image .= ':'.$tag;
                    }
                }

                $propertyAccessor->setValue($enhancedConfiguration, $path.$this->configuration['namePath'], $image);
            }
        }

        $configs[] = $enhancedConfiguration;

        return $configs;
    }

    /**
     * Get configurations at this path if accessible.
     *
     * @param array  $values
     * @param string $path
     *
     * @return array
     */
    private function getConfigurations($values, $path)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return array_filter($values, function ($value) use ($propertyAccessor, $path) {
            try {
                return is_array($value) && $propertyAccessor->getValue($value, $path);
            } catch (UnexpectedTypeException $e) {
                return false;
            }
        });
    }
}
