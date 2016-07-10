<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Pipe\ClusterIdentifierNotFound;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Authenticator\UserContext;

/**
 * @deprecated To be moved under `ContinuousPipe\River\Environment` namespace.
 */
class EnvironmentClient implements DeployedEnvironmentRepository
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
     * {@inheritdoc}
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

            // Convert Pipe's `Environment` objects to `DeployedEnvironment`s
            $deployedEnvironments = array_map(function (Environment $environment) use ($clusterIdentifier) {
                return new DeployedEnvironment(
                    $environment->getIdentifier(),
                    $clusterIdentifier,
                    $environment->getComponents()
                );
            }, $clusterEnvironments);

            $environments = array_merge($environments, $deployedEnvironments);
        }

        return $this->uniqueEnvironments($environments);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Flow $flow, DeployedEnvironment $environment)
    {
        $this->pipeClient->deleteEnvironment(
            new Client\DeploymentRequest\Target(
                $environment->getIdentifier(),
                $environment->getCluster()
            ),
            $flow->getContext()->getTeam(),
            $this->userContext->getCurrent()
        );
    }

    /**
     * @param Flow $flow
     *
     * @return array
     */
    private function findClusterIdentifiers(Flow $flow)
    {
        $tides = $this->tideRepository->findLastByFlow($flow, 10);
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
     * @param DeployedEnvironment[] $environments
     *
     * @return DeployedEnvironment[]
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
