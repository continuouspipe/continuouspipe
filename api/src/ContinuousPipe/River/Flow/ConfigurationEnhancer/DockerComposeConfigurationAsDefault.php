<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\DockerCompose\ComponentsResolver;
use ContinuousPipe\River\CodeRepository\DockerCompose\Configuration\PortIdentifier;
use ContinuousPipe\River\CodeRepository\DockerCompose\DockerComposeComponent;
use ContinuousPipe\River\CodeRepository\DockerCompose\ResolveException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use ContinuousPipe\River\Flow\ConfigurationEnhancer\Helper\TaskLocator;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\TideConfigurationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DockerComposeConfigurationAsDefault implements ConfigurationEnhancer
{
    use TaskLocator;

    /**
     * @var ComponentsResolver
     */
    private $componentsResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ComponentsResolver $componentsResolver
     * @param LoggerInterface    $logger
     */
    public function __construct(ComponentsResolver $componentsResolver, LoggerInterface $logger)
    {
        $this->componentsResolver = $componentsResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(FlatFlow $flow, CodeReference $codeReference, array $configs)
    {
        try {
            $dockerComposeComponents = $this->componentsResolver->resolve($flow, $codeReference);
        } catch (ResolveException $e) {
            $this->logger->warning('Unable to resolve the DockerCompose components', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'flow_uuid' => (string) $flow->getUuid(),
                'code_reference' => $codeReference,
            ]);

            return $configs;
        } catch (CodeRepositoryException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $enhancedConfig = $this->getEmptyConfiguration($configs);

        foreach ($this->getTaskPathsAndType($configs) as $path => $taskType) {
            // Initialize paths else it will break the configuration order
            $propertyAccessor->setValue($enhancedConfig, $path, []);
            if (!in_array($taskType, ['build', 'deploy'])) {
                continue;
            }

            try {
                $servicesConfiguration = $this->getServicesConfigurationForTask($taskType, $dockerComposeComponents);
            } catch (ResolveException $e) {
                $this->logger->info('Unable to resolve the service configuration for tasks', [
                    'exception' => $e,
                    'message' => $e->getMessage(),
                    'flow_uuid' => (string) $flow->getUuid(),
                    'code_reference' => $codeReference,
                    'task_path' => $path,
                ]);

                continue;
            }

            $servicesPath = $path.'[services]';
            $existingConfigurations = $this->getValuesAtPath($configs, $servicesPath);

            // If a configuration already exists, then only enhance the defined services
            if (!empty($existingConfigurations)) {
                $existingServices = array_reduce($existingConfigurations, function (array $services, array $config) {
                    return array_merge($services, array_keys($config));
                }, []);

                foreach ($servicesConfiguration as $serviceName => $config) {
                    if (!in_array($serviceName, $existingServices)) {
                        unset($servicesConfiguration[$serviceName]);
                    }
                }
            }

            $propertyAccessor->setValue($enhancedConfig, $servicesPath, $servicesConfiguration);
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
                ];

                if (!empty($imageName)) {
                    $configuration['image'] = $imageName;
                }
            } elseif ($taskType == 'deploy') {
                $configuration = [
                    'specification' => [
                        'ports' => [],
                        'environment_variables' => [],
                        'volumes' => [],
                        'volume_mounts' => [],
                    ],
                ];

                if (!empty($imageName)) {
                    $configuration['specification']['source'] = [
                        'image' => $imageName,
                    ];
                }

                if ($updatePolicy = $component->getUpdatePolicy()) {
                    $configuration['locked'] = $updatePolicy == 'lock';
                }

                if ($visibility = $component->getVisibility()) {
                    $configuration['specification']['accessibility'] = [
                        'from_external' => $visibility == 'public',
                    ];
                }

                if ($privileged = $component->isPrivileged()) {
                    $configuration['specification']['runtime_policy'] = [
                        'privileged' => true,
                    ];
                }

                if ($command = $component->getCommand()) {
                    if (!is_array($command)) {
                        $command = [$command];
                    }

                    $configuration['specification']['command'] = $command;
                }

                foreach ($component->getEnvironmentVariables() as $name => $value) {
                    $configuration['specification']['environment_variables'][] = [
                        'name' => $name,
                        'value' => $value,
                    ];
                }

                foreach ($component->getExposedPorts() as $exposedPort) {
                    $configuration['specification']['ports'][] = [
                        'identifier' => (string)PortIdentifier::fromNameAndPort($component->getName(), $exposedPort),
                        'port' => $exposedPort,
                    ];
                }

                try {
                    $volumes = $component->getVolumes();
                } catch (ResolveException $e) {
                    $volumes = [];
                }

                foreach ($volumes as $index => $volume) {
                    if (!$volume->isHostMount()) {
                        continue;
                    }

                    try {
                        $mountPath = $volume->getMountPath();
                        $hostPath = $volume->getHostPath();
                    } catch (ResolveException $e) {
                        continue;
                    }

                    $volumeName = $component->getName().'-volume-'.$index;
                    $configuration['specification']['volumes'][] = [
                        'type' => 'hostPath',
                        'path' => $hostPath,
                        'name' => $volumeName,
                    ];

                    $configuration['specification']['volume_mounts'][] = [
                        'name' => $volumeName,
                        'mount_path' => $mountPath,
                    ];
                }
            }

            $services[$component->getName()] = $configuration;
        }

        return $services;
    }
}
