<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\Pipe\ClusterIdentifierNotFound;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Authenticator\UserContext;

class EnvironmentClient
{
    /**
     * @var Client
     */
    private $pipeClient;

    /**
     * @var ClusterIdentifierResolver
     */
    private $clusterIdentifierResolver;

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
     * @param ClusterIdentifierResolver $clusterIdentifierResolver
     * @param UserContext               $userContext
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param TideRepository            $tideRepository
     */
    public function __construct(Client $pipeClient, ClusterIdentifierResolver $clusterIdentifierResolver, UserContext $userContext, EnvironmentNamingStrategy $environmentNamingStrategy, TideRepository $tideRepository)
    {
        $this->pipeClient = $pipeClient;
        $this->clusterIdentifierResolver = $clusterIdentifierResolver;
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
        foreach ($this->findClusterIdentifiers($flow) as $clusterIdentifier) {
            try {
                $clusterEnvironments = $this->pipeClient->getEnvironments($clusterIdentifier, $flow->getContext()->getTeam(), $this->userContext->getCurrent());
            } catch (ClusterNotFound $e) {
                $clusterEnvironments = [];
            }

            $environments = array_merge($environments, $clusterEnvironments);
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
    private function findClusterIdentifiers(Flow $flow)
    {
        $tides = $this->tideRepository->findByFlow($flow);
        $clusterIdentifiers = [];

        foreach ($tides as $tide) {
            try {
                $clusterIdentifiers[] = $this->clusterIdentifierResolver->getClusterIdentifier($tide);
            } catch (ClusterIdentifierNotFound $e) {
            }
        }

        return array_unique($clusterIdentifiers);
    }
}
