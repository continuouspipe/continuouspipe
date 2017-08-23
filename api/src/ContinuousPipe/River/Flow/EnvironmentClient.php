<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\Security\Authenticator\UserContext;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Client $pipeClient
     * @param ClusterIdentifierResolver $clusterIdentifierResolver
     * @param UserContext $userContext
     * @param BucketRepository $bucketRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $pipeClient,
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
        $promises = [];

        foreach ($this->findClusterIdentifiers($flow) as $clusterIdentifier) {
            $clusterEnvironmentsPromise = $this->findEnvironmentsLabelledByFlow($flow, $clusterIdentifier)->then(
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

            $promises[] = $clusterEnvironmentsPromise;
        }

        $environments = array_reduce(
            Promise\unwrap($promises),
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
    public function delete(Team $team, User $user, DeployedEnvironment $environment)
    {
        $this->pipeClient->deleteEnvironment(
            new Client\DeploymentRequest\Target(
                $environment->getIdentifier(),
                $environment->getCluster()
            ),
            $team,
            $user
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deletePod(FlatFlow $flow, string $clusterIdentifier, string $namespace, string $podName)
    {
        $this->pipeClient->deletePod(
            $flow->getTeam(),
            $this->userContext->getCurrent(),
            $clusterIdentifier,
            $namespace,
            $podName
        );
    }

    /**
     * @param FlatFlow $flow
     *
     * @return string[]
     */
    private function findClusterIdentifiers(FlatFlow $flow)
    {
        $teamBucketUuid = $flow->getTeam()->getBucketUuid();
        $credentialsBucket = $this->bucketRepository->find($teamBucketUuid);

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
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    private function findEnvironmentsLabelledByFlow(FlatFlow $flow, $clusterIdentifier)
    {
        return $this->pipeClient->getEnvironmentsLabelled(
            $clusterIdentifier,
            $flow->getTeam(),
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
