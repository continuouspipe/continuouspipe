<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

class CloudflareConfigurator implements EndpointConfigurator
{
    /**
     * @var HostnameResolver
     */
    private $hostnameResolver;

    public function __construct(HostnameResolver $hostnameResolver)
    {
        $this->hostnameResolver = $hostnameResolver;
    }

    /**
     * @param array $endpointConfiguration
     * @return array
     * @throws TideGenerationException
     */
    public function checkConfiguration(array $endpointConfiguration)
    {
        if (!isset($endpointConfiguration['cloud_flare_zone'])) {
            return;
        }

        if (isset($endpointConfiguration['cloud_flare_zone']['record_suffix'])) {
            if ($this->hostnameResolver->suffixTooLong($endpointConfiguration['cloud_flare_zone']['record_suffix'])) {
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
    public function addHost(array $endpointConfiguration, TaskContext $context)
    {
        if (isset($endpointConfiguration['cloud_flare_zone']['record_suffix'])) {
            $endpointConfiguration['cloud_flare_zone']['host']['expression'] =
                $this->hostnameResolver->generateHostExpression($endpointConfiguration['cloud_flare_zone']['record_suffix']);
        }

        if (isset($endpointConfiguration['cloud_flare_zone']['host'])) {
            $endpointConfiguration['cloud_flare_zone']['hostname'] =
                $this->hostnameResolver->resolveHostname($context, $endpointConfiguration['cloud_flare_zone']['host']);
        }

        return $endpointConfiguration;
    }

}