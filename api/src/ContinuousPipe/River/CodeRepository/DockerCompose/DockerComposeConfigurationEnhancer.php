<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DockerComposeConfigurationEnhancer implements Flow\ConfigurationEnhancer
{
    /**
     * @var ProjectParser
     */
    private $projectParser;

    /**
     * @var FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @param FileSystemResolver $fileSystemResolver
     * @param ProjectParser      $projectParser
     */
    public function __construct(FileSystemResolver $fileSystemResolver, ProjectParser $projectParser)
    {
        $this->projectParser = $projectParser;
        $this->fileSystemResolver = $fileSystemResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Flow $flow, CodeReference $codeReference, array $configs)
    {
        $fileSystem = $this->fileSystemResolver->getFileSystem($codeReference, $flow->getContext()->getUser());
        $dockerComposeComponents = [];

        try {
            foreach ($this->projectParser->parse($fileSystem, $codeReference->getBranch()) as $name => $raw) {
                $dockerComposeComponents[] = DockerComposeComponent::fromParsed($name, $raw);
            }
        } catch (FileNotFound $e) {
            return $configs;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $enhancedConfig = [];

        foreach ($this->getTaskPathsByType($configs) as $path => $taskType) {
            // Initialize paths else it will break the configuration order
            $propertyAccessor->setValue($enhancedConfig, $path, []);
            if (!in_array($taskType, ['build', 'deploy'])) {
                continue;
            }

            $servicesConfiguration = $this->getServicesConfigurationForTask($taskType, $dockerComposeComponents);

            $propertyAccessor->setValue($enhancedConfig, $path.'[services]', $servicesConfiguration);
        }

        array_unshift($configs, $enhancedConfig);

        return $configs;
    }

    /**
     * @param array $configs
     *
     * @return array
     */
    private function getTaskPathsByType(array $configs)
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

        return $paths;
    }

    /**
     * @param string                   $taskType
     * @param DockerComposeComponent[] $dockerComposeComponents
     *
     * @return array
     */
    private function getServicesConfigurationForTask($taskType, array $dockerComposeComponents)
    {
        $services = [];

        foreach ($dockerComposeComponents as $component) {
            $configuration = [];

            if ($taskType == 'build') {
                if (!$component->hasToBeBuilt()) {
                    continue;
                }

                $configuration = [
                    'build_directory' => $component->getBuildDirectory(),
                    'docker_file_path' => $component->getDockerfilePath(),
                ];

                try {
                    $configuration['image'] = $component->getImageName();
                } catch (ResolveException $e) {
                }
            } elseif ($taskType == 'deploy') {
                if ($updatePolicy = $component->getUpdatePolicy()) {
                    $configuration['update'] = $updatePolicy;
                }
                if ($visibility = $component->getVisibility()) {
                    $configuration['visibility'] = $visibility;
                }
            }

            $services[$component->getName()] = $configuration;
        }

        return $services;
    }
}
