<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Pipe\ProviderNameNotFound;
use ContinuousPipe\River\Pipe\ProviderNameResolver;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param Client                    $pipeClient
     * @param ProviderNameResolver      $providerNameResolver
     * @param UserContext               $userContext
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param TideRepository            $tideRepository
     */
    public function __construct(Client $pipeClient, ProviderNameResolver $providerNameResolver, UserContext $userContext, EnvironmentNamingStrategy $environmentNamingStrategy, TideRepository $tideRepository)
    {
        $this->pipeClient = $pipeClient;
        $this->providerNameResolver = $providerNameResolver;
        $this->userContext = $userContext;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->tideRepository = $tideRepository;
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
        $environments = [];
        foreach ($this->findProviderNames($flow) as $providerName) {
            $environments = array_merge(
                $environments,
                $this->pipeClient->getEnvironments($providerName, $this->userContext->getCurrent())
            );
        }

        $matchingEnvironments = array_filter($environments, function (Environment $environment) use ($flow) {
            return $this->environmentNamingStrategy->isEnvironmentPartOfFlow($flow->getUuid(), $environment);
        });

        return array_values($matchingEnvironments);
    }

    /**
     * @param Flow $flow
     *
     * @return array
     */
    private function findProviderNames(Flow $flow)
    {
        $tides = $this->tideRepository->findByFlow($flow);
        $providerNames = [];

        foreach ($tides as $tide) {
            try {
                $providerNames[] = $this->providerNameResolver->getProviderName($tide);
            } catch (ProviderNameNotFound $e) {
            }
        }

        return array_unique($providerNames);
    }
}
