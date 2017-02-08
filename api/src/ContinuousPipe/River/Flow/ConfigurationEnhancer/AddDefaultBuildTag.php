<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use Cocur\Slugify\Slugify;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AddDefaultBuildTag implements ConfigurationEnhancer
{
    use ConfigurationEnhancer\Helper\TaskLocator;

    /**
     * @var string
     */
    const DOCKER_TAG_REGEX = '[a-z0-9]+(?:[._-][a-z0-9]+)*';

    /**
     * {@inheritdoc}
     */
    public function enhance(FlatFlow $flow, CodeReference $codeReference, array $configs)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $taskPathsAndTypes = $this->getTaskPathsAndType($configs);
        $enhancedConfig = $this->getEmptyConfiguration($configs);

        // Get all the build task paths
        $buildPaths = array_filter($taskPathsAndTypes, function ($type) {
            return $type == 'build';
        });

        $builtServiceNames = $this->getServiceNames($configs, array_keys($buildPaths));
        foreach ($buildPaths as $path => $taskType) {
            foreach ($builtServiceNames as $serviceName) {
                $pathPrefixes = $this->expandSelector($configs, $path.'[services]['.$serviceName.'][steps][*]');
                array_unshift($pathPrefixes, $path.'[services]['.$serviceName.']');

                foreach ($pathPrefixes as $pathPrefix) {
                    // Get the image name values
                    $values = $this->getValuesAtPath($configs, $pathPrefix.'[image]');

                    // If there's no image name defined, we won't add the tag
                    if (count($values) == 0) {
                        if (strpos($pathPrefix, '][steps][') !== false) {
                            // Add a placeholder value to ensure that the order of the steps is kept
                            $propertyAccessor->setValue(
                                $enhancedConfig,
                                $pathPrefix,
                                []
                            );
                        }

                        continue;
                    }

                    // If there's no image name with tag name, then add one
                    $imageTagPath = $pathPrefix.'[tag]';
                    $tags = $this->getValuesAtPath($configs, $imageTagPath);
                    if (count($tags) == 0) {
                        $tag = $this->getDefaultImageTag(
                            $codeReference,
                            $this->getNamingStrategy($configs, $pathPrefix.'[naming_strategy]')
                        );

                        $propertyAccessor->setValue($enhancedConfig, $imageTagPath, $tag);
                    }
                }
            }
        }

        array_unshift($configs, $enhancedConfig);

        return $configs;
    }

    /**
     * @param CodeReference $codeReference
     * @param string        $namingStrategy
     *
     * @return string
     */
    private function getDefaultImageTag(CodeReference $codeReference, $namingStrategy)
    {
        if ($namingStrategy == 'branch') {
            $tag = $codeReference->getBranch();
            if ($tag && !preg_match('#^'.self::DOCKER_TAG_REGEX.'$#', $tag)) {
                $tag = (new Slugify())->slugify($tag);
            }

            return $tag;
        }

        return $codeReference->getCommitSha();
    }

    /**
     * @param array  $configs
     * @param string $namingStrategyPath
     *
     * @return string|null
     */
    private function getNamingStrategy($configs, $namingStrategyPath)
    {
        $values = $this->getValuesAtPath($configs, $namingStrategyPath);

        if (count($values) == 0) {
            return;
        }

        return end($values);
    }
}
