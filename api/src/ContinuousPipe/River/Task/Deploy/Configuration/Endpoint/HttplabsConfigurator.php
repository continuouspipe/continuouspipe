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
            if ($this->hostnameResolver->suffixTooLong($endpointConfiguration['httplabs']['record_suffix'])) {
                throw new TideGenerationException(
                    sprintf(
                        'The httplabs record_suffix cannot be more than %s characters long',
                        $this->hostnameResolver->maxSuffixLength()
                    )
                );
            }
            return;
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
        if (isset($endpointConfiguration['httplabs']['record_suffix'])) {
            $endpointConfiguration['httplabs']['host']['expression'] =
                $this->hostnameResolver->generateHostExpression($endpointConfiguration['httplabs']['record_suffix']);
        }
        if (isset($endpointConfiguration['httplabs']['host'])) {
            $endpointConfiguration['httplabs']['incoming'] =
                $this->hostnameResolver->resolveHostname($context, $endpointConfiguration['httplabs']['host']);
        }

        return $endpointConfiguration;
    }

}