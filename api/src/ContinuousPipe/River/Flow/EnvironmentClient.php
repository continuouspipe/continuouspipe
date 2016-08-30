<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\River\Flow;
use ContinuousPipe\Security\Authenticator\UserContext;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;

/**
 * @deprecated To be moved under `ContinuousPipe\River\Environment` namespace
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
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param Client                    $pipeClient
     * @param ClusterIdentifierResolver $clusterIdentifierResolver
     * @param UserContext               $userContext
     * @param BucketRepository          $bucketRepository
     */
    public function __construct(Client $pipeClient, ClusterIdentifierResolver $clusterIdentifierResolver, UserContext $userContext, BucketRepository $bucketRepository)
    {
        $this->pipeClient = $pipeClient;
        $this->clusterIdentifierResolver = $clusterIdentifierResolver;
        $this->userContext = $userContext;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(Flow $flow)
    {
        $environments = [];

        foreach ($this->findClusterIdentifiers($flow) as $clusterIdentifier) {
            try {
                $clusterEnvironments = $this->findEnvironmentsLabelledByFlow($flow, $clusterIdentifier);
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
     * @return string[]
     */
    private function findClusterIdentifiers(Flow $flow)
    {
        $teamBucketUuid = $flow->getContext()->getTeam()->getBucketUuid();
        $credentialsBucket = $this->bucketRepository->find($teamBucketUuid);

        return $credentialsBucket->getClusters()->map(function (Cluster $cluster) {
            return $cluster->getIdentifier();
        })->toArray();
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
