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

        if (isset($endpointConfiguration['ingress']['host_suffix'])) {
            if ($this->hostnameResolver->suffixTooLong($endpointConfiguration['ingress']['host_suffix'])) {
                throw new TideGenerationException(
                    sprintf(
                        'The ingress host_suffix cannot be more than %s characters long',
                        $this->hostnameResolver->maxSuffixLength()
                    )
                );
            }
        } elseif (!isset($endpointConfiguration['ingress']['host']['expression'])) {
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
                            $context,
                            $endpointConfiguration['ingress']['host']
                        )
                    ]
                ];
        }

        return $endpointConfiguration;
    }
}
