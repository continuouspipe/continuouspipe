<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

class IngressConfigurator implements EndpointConfigurationEnhancer
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
     * {@inheritdoc}
     */
    public function enhance(array $endpointConfiguration, TaskContext $context)
    {
        if (!isset($endpointConfiguration['ingress'])) {
            return $endpointConfiguration;
        }

        if (!isset($endpointConfiguration['ingress']['host']['expression']) && !isset($endpointConfiguration['ingress']['host_suffix'])) {
            throw new TideGenerationException('The ingress needs a host_suffix or a host expression');
        }

        if (isset($endpointConfiguration['ingress']['host_suffix'])) {
            $endpointConfiguration['ingress']['host']['expression'] =
                $this->hostnameResolver->generateHostExpression($endpointConfiguration['ingress']['host_suffix']);
        }

        if (isset($endpointConfiguration['ingress']['host'])) {
            $endpointConfiguration['ingress']['rules'] =
                [
                    [
                        'host' => $this->hostnameResolver->resolveHostname(
                            $context->getFlowUuid(),
                            $context->getCodeReference(),
                            $endpointConfiguration['ingress']['host']['expression']
                        )
                    ]
                ];
        }

        return $endpointConfiguration;
    }
}
