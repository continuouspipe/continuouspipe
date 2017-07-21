<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

class HttplabsConfigurator implements EndpointConfigurationEnhancer
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
        if (!isset($endpointConfiguration['httplabs'])) {
            return $endpointConfiguration;
        }

        if (isset($endpointConfiguration['httplabs']['record_suffix'])) {
            $this->hostnameResolver->checkSuffixLength('httplabs', $endpointConfiguration);
        }

        $endpointConfiguration['httplabs'] = $this->hostnameResolver->addHost(
            $endpointConfiguration['httplabs'],
            $context,
            'incoming'
        );

        return $endpointConfiguration;
    }
}
