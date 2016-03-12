<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\DockerCompose\ComponentsResolver;
use ContinuousPipe\River\CodeRepository\DockerCompose\DockerComposeComponent;
use ContinuousPipe\River\CodeRepository\DockerCompose\ResolveException;
use ContinuousPipe\River\Flow;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DockerComposeConfigurationAsDefault implements Flow\ConfigurationEnhancer
{
    use Flow\ConfigurationEnhancer\Helper\TaskLocator;

    /**
     * @var ComponentsResolver
     */
    private $componentsResolver;

    /**
     * @param ComponentsResolver $componentsResolver
     */
    public function __construct(ComponentsResolver $componentsResolver)
    {
        $this->componentsResolver = $componentsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Flow $flow, CodeReference $codeReference, array $configs)
    {
        try {
            $dockerComposeComponents = $this->componentsResolver->resolve($codeReference, $flow->getContext()->getTeam());
        } catch (ResolveException $e) {
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
            $servicesPath = $path.'[services]';

            // If a configuration already exists, then only enhance the defined services
            $existingConfigurations = $this->getValuesAtPath($configs, $servicesPath);
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
                    $configuration['specification']['command'] = [$command];
                }

                foreach ($component->getEnvironmentVariables() as $name => $value) {
                    $configuration['specification']['environment_variables'][] = [
                        'name' => $name,
                        'value' => $value,
                    ];
                }

                foreach ($component->getExposedPorts() as $exposedPort) {
                    $configuration['specification']['ports'][] = [
                        'identifier' => $component->getName().((string) $exposedPort),
                        'port' => $exposedPort,
                    ];
                }

                foreach ($component->getVolumes() as $index => $volume) {
                    if (!$volume->isHostMount()) {
                        continue;
                    }

                    $volumeName = $component->getName().'-volume-'.$index;
                    $configuration['specification']['volumes'][] = [
                        'type' => 'hostPath',
                        'path' => $volume->getHostPath(),
                        'name' => $volumeName,
                    ];

                    $configuration['specification']['volume_mounts'][] = [
                        'name' => $volumeName,
                        'mount_path' => $volume->getMountPath(),
                    ];
                }
            }

            $services[$component->getName()] = $configuration;
        }

        return $services;
    }
}
