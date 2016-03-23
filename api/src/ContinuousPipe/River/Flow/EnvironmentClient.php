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
                $clusterEnvironments = array_merge(
                    $this->findEnvironmentStartingWithFlowUuid($flow, $clusterIdentifier),
                    $this->findEnvironmentsLabelledByFlow($flow, $clusterIdentifier)
                );
            } catch (ClusterNotFound $e) {
                $clusterEnvironments = [];
            }

            $environments = array_merge($environments, $clusterEnvironments);
        }

        return $this->uniqueEnvironments($environments);
    }

    /**
     * @param Flow $flow
     *
     * @return array
     */
    private function findClusterIdentifiers(Flow $flow)
    {
        $tides = $this->tideRepository->findByFlowUuid($flow->getUuid());
        $clusterIdentifiers = [];

        foreach ($tides as $tide) {
            try {
                $clusterIdentifiers[] = $this->clusterIdentifierResolver->getClusterIdentifier($tide);
            } catch (ClusterIdentifierNotFound $e) {
            }
        }

        return array_unique($clusterIdentifiers);
    }

    /**
     * @param Flow   $flow
     * @param string $clusterIdentifier
     *
     * @return array
     *
     * @deprecated We should only use the `findEnvironmentsTaggedByFlow` method as it's definitely more
     *             efficient on large cluster, and it allows to have custom environment names.
     */
    private function findEnvironmentStartingWithFlowUuid(Flow $flow, $clusterIdentifier)
    {
        return array_filter(
            $this->pipeClient->getEnvironments($clusterIdentifier, $flow->getContext()->getTeam(), $this->userContext->getCurrent()),
            function (Environment $environment) use ($flow) {
                return $this->environmentNamingStrategy->isEnvironmentPartOfFlow($flow->getUuid(), $environment);
            }
        );
    }

    /**
     * Find environments labelled by the flow UUID.
     *
     * @param Flow   $flow
     * @param string $clusterIdentifier
     *
     * @return Environment[]
     */
    private function findEnvironmentsLabelledByFlow(Flow $flow, $clusterIdentifier)
    {
        return $this->pipeClient->getEnvironmentsLabelled(
            $clusterIdentifier,
            $flow->getContext()->getTeam(),
            $this->userContext->getCurrent(),
            [
                'flow' => (string) $flow->getUuid(),
            ]
        );
    }

    /**
     * @param Environment[] $environments
     *
     * @return Environment[]
     */
    private function uniqueEnvironments(array $environments)
    {
        $uniqueEnvironments = [];

        foreach ($environments as $environment) {
            if (!array_key_exists($environment->getIdentifier(), $uniqueEnvironments)) {
                $uniqueEnvironments[$environment->getIdentifier()] = $environment;
            }
        }

        return array_values($uniqueEnvironments);
    }
}
