<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Client\PipeClientException;
use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Environment\DeployedEnvironmentException;
use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\Security\User\UserContext;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise;
use Psr\Log\LoggerInterface;

/**
 * @deprecated To be moved under `ContinuousPipe\River\Environment` namespace
 */
class EnvironmentClient implements DeployedEnvironmentRepository
{
    /**
     * @var \ContinuousPipe\Pipe\Client\Client
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \ContinuousPipe\Pipe\Client\Client $pipeClient
     * @param ClusterIdentifierResolver $clusterIdentifierResolver
     * @param \ContinuousPipe\Security\User\UserContext $userContext
     * @param BucketRepository $bucketRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client\Client $pipeClient,
        ClusterIdentifierResolver $clusterIdentifierResolver,
        UserContext $userContext,
        BucketRepository $bucketRepository,
        LoggerInterface $logger
    ) {
        $this->pipeClient = $pipeClient;
        $this->clusterIdentifierResolver = $clusterIdentifierResolver;
        $this->userContext = $userContext;
        $this->bucketRepository = $bucketRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(FlatFlow $flow)
    {
        $environments = array_reduce(
            Promise\unwrap(
                array_map(function (string $clusterIdentifier) use ($flow) {
                    return $this->findByFlowAndCluster($flow, $clusterIdentifier);
                }, $this->findClusterIdentifiers($flow))
            ),
            function ($carry, array $item) {
                return array_merge($carry, $item);
            },
            []
        );

        return $this->uniqueEnvironments($environments);
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowAndCluster(FlatFlow $flow, string $clusterIdentifier) : PromiseInterface
    {
        return $this->findEnvironmentsLabelledByFlow($flow, $clusterIdentifier)->then(
            function (array $clusterEnvironments) use ($clusterIdentifier) {
                // Convert Pipe's `Environment` objects to `DeployedEnvironment`s
                return array_map(function (Environment $environment) use ($clusterIdentifier) {
                    return new DeployedEnvironment(
                        $environment->getIdentifier(),
                        $clusterIdentifier,
                        $environment->getComponents(),
                        $environment->getStatus()
                    );
                }, $clusterEnvironments);
            },
            function (\Throwable $e) use ($clusterIdentifier) {
                $this->logger->warning(
                    'Fetching environment list from Pipe failed.',
                    ['exception' => $e, 'cluster' => $clusterIdentifier]
                );

                return [];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Team $team, User $user, DeployedEnvironment $environment)
    {
        try {
            $this->pipeClient->deleteEnvironment(
                new DeploymentRequest\Target(
                    $environment->getIdentifier(),
                    $environment->getCluster()
                ),
                $team,
                $user
            );
        } catch (PipeClientException $e) {
            throw new DeployedEnvironmentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletePod(FlatFlow $flow, string $clusterIdentifier, string $namespace, string $podName)
    {
        try {
            $this->pipeClient->deletePod(
                $flow->getTeam(),
                $this->userContext->getCurrent(),
                $clusterIdentifier,
                $namespace,
                $podName
            );
        } catch (PipeClientException $e) {
            throw new DeployedEnvironmentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param FlatFlow $flow
     *
     * @throws DeployedEnvironmentException
     *
     * @return string[]
     */
    private function findClusterIdentifiers(FlatFlow $flow)
    {
        $teamBucketUuid = $flow->getTeam()->getBucketUuid();

        try {
            $credentialsBucket = $this->bucketRepository->find($teamBucketUuid);
        } catch (BucketNotFound $e) {
            throw new DeployedEnvironmentException($e->getMessage(), $e->getCode(), $e);
        }

        return $credentialsBucket->getClusters()->map(function (Cluster $cluster) {
            return $cluster->getIdentifier();
        })->toArray();
    }

    /**
     * Find environments labelled by the flow UUID.
     *
     * @param FlatFlow $flow
     * @param string   $clusterIdentifier
     *
     * @throws DeployedEnvironmentException
     *
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    private function findEnvironmentsLabelledByFlow(FlatFlow $flow, $clusterIdentifier)
    {
        try {
            return $this->pipeClient->getEnvironmentsLabelled(
                $clusterIdentifier,
                $flow->getTeam(),
                [
                    'flow' => (string)$flow->getUuid(),
                ]
            );
        } catch (PipeClientException $e) {
            throw new DeployedEnvironmentException($e->getMessage(), $e->getCode(), $e);
        }
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
