<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

class HttplabsConfigurator implements EndpointConfigurator
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
        if (!isset($endpointConfiguration['httplabs'])) {
            return;
        }

        if (isset($endpointConfiguration['httplabs']['record_suffix'])) {
            $this->hostnameResolver->checkSuffixLength('httplabs', $endpointConfiguration);
        }
    }

    /**
     * @param array $endpointConfiguration
     * @param TaskContext $context
     * @return array
     * @throws TideGenerationException
     */
    public function addHost(array $endpointConfiguration, TaskContext $context)
    {
        if (!isset($endpointConfiguration['httplabs'])) {
            return $endpointConfiguration;
        }

        $endpointConfiguration['httplabs'] = $this->hostnameResolver->addHost(
            $endpointConfiguration['httplabs'],
            $context,
            'incoming'
        );

        return $endpointConfiguration;
    }
}
