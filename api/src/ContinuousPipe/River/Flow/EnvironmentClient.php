<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Pipe\ProviderNameResolver;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\User\Security\UserContext;

class EnvironmentClient
{
    /**
     * @var Client
     */
    private $pipeClient;

    /**
     * @var ProviderNameResolver
     */
    private $providerNameResolver;

    /**
     * @var UserContext
     */
    private $userContext;

    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @param Client                    $pipeClient
     * @param ProviderNameResolver      $providerNameResolver
     * @param UserContext               $userContext
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     */
    public function __construct(Client $pipeClient, ProviderNameResolver $providerNameResolver, UserContext $userContext, EnvironmentNamingStrategy $environmentNamingStrategy)
    {
        $this->pipeClient = $pipeClient;
        $this->providerNameResolver = $providerNameResolver;
        $this->userContext = $userContext;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
    }

    /**
     * Get environments for the given flow.
     *
     * @param Flow $flow
     *
     * @return Environment[]
     */
    public function findByFlow(Flow $flow)
    {
        $providerName = $this->providerNameResolver->getProviderName($flow);
        $environments = $this->pipeClient->getEnvironments($providerName, $this->userContext->getCurrent());
        $matchingEnvironments = array_filter($environments, function (Environment $environment) use ($flow) {
            return $this->environmentNamingStrategy->isEnvironmentPartOfFlow($flow->getUuid(), $environment);
        });

        return array_values($matchingEnvironments);
    }
}
