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
            $this->hostnameResolver->checkSuffixLength('cloud_flare_zone', $endpointConfiguration);

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
        if (!isset($endpointConfiguration['cloud_flare_zone'])) {
            return $endpointConfiguration;
        }

        $endpointConfiguration['cloud_flare_zone'] = $this->hostnameResolver->addHost(
            $endpointConfiguration['cloud_flare_zone'],
            $context,
            'hostname'
        );

        return $endpointConfiguration;
    }

}