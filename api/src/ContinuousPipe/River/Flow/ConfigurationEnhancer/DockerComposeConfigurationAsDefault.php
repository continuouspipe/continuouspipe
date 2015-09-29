<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\DockerCompose\DockerComposeComponent;
use ContinuousPipe\River\CodeRepository\DockerCompose\ResolveException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DockerComposeConfigurationAsDefault implements Flow\ConfigurationEnhancer
{
    use Flow\ConfigurationEnhancer\Helper\TaskLocator;

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

        foreach ($this->getTaskPathsAndType($configs) as $path => $taskType) {
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

            try {
                $imageName = $component->getImageName();
            } catch (ResolveException $e) {
                $imageName = null;
            }

            if ($taskType == 'build') {
                if (!$component->hasToBeBuilt()) {
                    continue;
                }

                $configuration = [
                    'build_directory' => $component->getBuildDirectory(),
                    'docker_file_path' => $component->getDockerfilePath(),
                    'image' => $imageName,
                ];
            } elseif ($taskType == 'deploy') {
                $configuration = [
                    'specification' => [
                        'source' => [
                            'image' => $imageName,
                        ],
                        'ports' => [],
                    ],
                ];

                if ($updatePolicy = $component->getUpdatePolicy()) {
                    $configuration['locked'] = $updatePolicy == 'lock';
                }

                if ($visibility = $component->getVisibility()) {
                    $configuration['specification']['accessibility'] = [
                        'from_external' => $visibility == 'public',
                    ];
                }

                foreach ($component->getExposedPorts() as $exposedPort) {
                    $configuration['specification']['ports'][] = [
                        'identifier' => $component->getName().((string) $exposedPort),
                        'port' => $exposedPort,
                    ];
                }
            }

            $services[$component->getName()] = $configuration;
        }

        return $services;
    }
}
