<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration;

use ContinuousPipe\DomainName\Transformer;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Extension;
use ContinuousPipe\River\Flow\Variable\FlowVariableResolver;
use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\TideConfigurationException;
use JMS\Serializer\SerializerInterface;

class ComponentFactory
{
    const MAX_HOST_LENGTH = 64;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var FlowVariableResolver
     */
    private $flowVariableResolver;

    public function __construct(FlowVariableResolver $flowVariableResolver, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->flowVariableResolver = $flowVariableResolver;
    }

    /**
     * @parem TaskContext $context
     * @param string      $name
     * @param array       $configuration
     *
     * @return Component
     */
    public function createFromConfiguration(TaskContext $context, string $name, array $configuration)
    {
        $component = new Component(
            $name,
            $name,
            $this->getSpecification($configuration),
            $this->getExtensions($configuration),
            [],
            null,
            $this->getDeploymentStrategy($configuration),
            $this->getEndpoints($context, $configuration)
        );

        return $component;
    }

    /**
     * Get component specification from the configuration.
     *
     * @param array $configuration
     *
     * @return Component\Specification
     */
    private function getSpecification(array $configuration)
    {
        $jsonEncodedSpecification = json_encode($configuration['specification']);

        return $this->serializer->deserialize($jsonEncodedSpecification, Component\Specification::class, 'json');
    }

    /**
     * Get component endpoints from the configuration.
     *
     * @param array $configuration
     *
     * @return Component\Endpoint[]
     */
    private function getEndpoints(TaskContext $context, array $configuration)
    {
        if (!array_key_exists('endpoints', $configuration)) {
            return [];
        }

        // Resolve hosts expression
        $configuration['endpoints'] = array_map(function (array $endpointConfiguration) use ($context) {
            $this->checkIngressConfiguration($endpointConfiguration);
            $this->checkCloudFlareConfiguration($endpointConfiguration);

            return $this->addCloudFlareHost($this->addIngressHost($endpointConfiguration, $context), $context);
        }, $configuration['endpoints']);

        $jsonEncodedEndpoints = json_encode($configuration['endpoints']);

        return $this->serializer->deserialize($jsonEncodedEndpoints, sprintf('array<%s>', Component\Endpoint::class), 'json');
    }

    /**
     * Get component deployment strategy from the configuration.
     *
     * @param array $configuration
     *
     * @return Component\DeploymentStrategy|null
     */
    private function getDeploymentStrategy(array $configuration)
    {
        if (!array_key_exists('deployment_strategy', $configuration)) {
            return;
        }

        $jsonEncoded = json_encode($configuration['deployment_strategy']);

        return $this->serializer->deserialize($jsonEncoded, Component\DeploymentStrategy::class, 'json');
    }

    /**
     * Returns the deserialized extension objects.
     *
     * @param array $configuration
     *
     * @return Extension[]
     */
    private function getExtensions(array $configuration)
    {
        if (!array_key_exists('extensions', $configuration)) {
            return [];
        }

        $normalizedExtensions = [];
        foreach ($configuration['extensions'] as $name => $extension) {
            $extension['name'] = $name;

            $normalizedExtensions[] = $extension;
        }

        $jsonEncodedExtensions = json_encode($normalizedExtensions);

        return $this->serializer->deserialize(
            $jsonEncodedExtensions,
            sprintf('array<%s>', Extension::class),
            'json'
        );
    }

    /**
     * @param array $endpointConfiguration
     * @return array
     * @throws TideGenerationException
     */
    private function checkIngressConfiguration(array $endpointConfiguration)
    {
        if (!isset($endpointConfiguration['ingress'])) {
            return;
        }

        if (isset($endpointConfiguration['ingress']['host_suffix'])) {
            if (mb_strlen($endpointConfiguration['ingress']['host_suffix']) > $this->maxSuffixLength()) {
                throw new TideGenerationException(sprintf('The ingress host_suffix cannot be more than %s characters long', $this->maxSuffixLength()));
            }
            return;
        }

        if (isset($endpointConfiguration['ingress']['host']['expression'])) {
            return;
        }

        throw new TideGenerationException('The ingress needs a host_suffix or a host expression');
    }

    /**
     * @param array $endpointConfiguration
     * @return array
     * @throws TideGenerationException
     */
    private function checkCloudFlareConfiguration(array $endpointConfiguration)
    {
        if (!isset($endpointConfiguration['cloud_flare_zone'])) {
            return;
        }

        if (isset($endpointConfiguration['cloud_flare_zone']['record_suffix'])) {
            if (mb_strlen($endpointConfiguration['cloud_flare_zone']['record_suffix']) > $this->maxSuffixLength()) {
                throw new TideGenerationException(sprintf('The cloud_flare_zone record_suffix cannot be more than %s characters long', $this->maxSuffixLength()));
            }
            return;
        }

        if (isset($endpointConfiguration['cloud_flare_zone']['host']['expression'])) {
            return;
        }

        if (isset($endpointConfiguration['ingress'])) {
            return;
        }

        throw new TideGenerationException('The cloud_flare_zone needs a record_suffix or a host expression');
    }

    /**
     * @param array $endpointConfiguration
     * @param TaskContext $context
     * @return array
     * @throws TideGenerationException
     */
    public function addIngressHost(array $endpointConfiguration, TaskContext $context)
    {
        if (isset($endpointConfiguration['ingress']['host_suffix'])) {
            $endpointConfiguration['ingress']['host']['expression'] =
                $this->generateHostExpression($endpointConfiguration['ingress']['host_suffix']);
        }

        if (isset($endpointConfiguration['ingress']['host'])) {
            $endpointConfiguration['ingress']['rules'] =
                [['host' => $this->resolveHostname($context, $endpointConfiguration['ingress']['host'])]];
        }

        return $endpointConfiguration;
    }

    /**
     * @param array $endpointConfiguration
     * @param TaskContext $context
     * @return array
     * @throws TideGenerationException
     */
    public function addCloudFlareHost(array $endpointConfiguration, TaskContext $context)
    {
        if (isset($endpointConfiguration['cloud_flare_zone']['record_suffix'])) {
            $endpointConfiguration['cloud_flare_zone']['host']['expression'] =
                $this->generateHostExpression($endpointConfiguration['cloud_flare_zone']['record_suffix']);
        }

        if (isset($endpointConfiguration['cloud_flare_zone']['host'])) {
            $endpointConfiguration['cloud_flare_zone']['hostname'] =
                $this->resolveHostname($context, $endpointConfiguration['cloud_flare_zone']['host']);
        }

        return $endpointConfiguration;
    }

    /**
     * @param TaskContext $context
     * @param array $hostConfiguration
     * @return mixed
     * @throws TideGenerationException
     */
    private function resolveHostname(TaskContext $context, array $hostConfiguration)
    {
        try {
            return $this->flowVariableResolver->resolveExpression(
                $hostConfiguration['expression'],
                $this->flowVariableResolver->createContext($context->getFlowUuid(), $context->getCodeReference())
            );
        } catch (TideConfigurationException $e) {
            throw new TideGenerationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $hostSuffix
     * @return string
     */
    private function generateHostExpression(string $hostSuffix): string
    {
        return sprintf(
            'hash_long_domain_prefix(slugify(code_reference.branch), %s) ~ "%s"',
            self::MAX_HOST_LENGTH - mb_strlen($hostSuffix),
            $hostSuffix
        );
    }

    /**
     * @return int
     */
    private function maxSuffixLength()
    {
        return self::MAX_HOST_LENGTH - Transformer::HOST_HASH_LENGTH;
    }
}
