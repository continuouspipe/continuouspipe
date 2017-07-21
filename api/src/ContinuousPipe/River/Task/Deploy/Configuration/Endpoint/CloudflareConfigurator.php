<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

class CloudflareConfigurator implements EndpointConfigurationEnhancer
{
    /**
     * @var HostnameResolver
     */
    private $hostnameResolver;

    /**
     * @param HostnameResolver $hostnameResolver
     */
    public function __construct(HostnameResolver $hostnameResolver)
    {
        $this->hostnameResolver = $hostnameResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(array $endpointConfiguration, TaskContext $context)
    {
        if (!isset($endpointConfiguration['cloud_flare_zone'])) {
            return $endpointConfiguration;
        }

        if (isset($endpointConfiguration['cloud_flare_zone']['record_suffix'])) {
            $this->hostnameResolver->checkSuffixLength('cloud_flare_zone', $endpointConfiguration);
        }

        if (!isset($endpointConfiguration['cloud_flare_zone']['host']['expression'])
            && !isset($endpointConfiguration['cloud_flare_zone']['record_suffix'])
            && !isset($endpointConfiguration['ingress'])) {
            throw new TideGenerationException('The cloud_flare_zone needs a record_suffix or a host expression');
        }

        $endpointConfiguration['cloud_flare_zone'] = $this->hostnameResolver->addHost(
            $endpointConfiguration['cloud_flare_zone'],
            $context,
            'hostname'
        );

        return $endpointConfiguration;
    }
}
