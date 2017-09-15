<?php

namespace ContinuousPipe\River\Flex\AsFeature\CommandHandler;

use ContinuousPipe\Events\Transaction\TransactionManager;
use ContinuousPipe\QuayIo\QuayClient;
use ContinuousPipe\QuayIo\RepositoryAlreadyExists;
use ContinuousPipe\QuayIo\RobotAccount;
use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flex\Cluster\ClusterResolver;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Team\Team;

class DoActivateFlex
{
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;
    /**
     * @var BucketRepository
     */
    private $bucketRepository;
    /**
     * @var ClusterResolver
     */
    private $clusterResolver;
    /**
     * @var TransactionManager
     */
    private $flowTransactionManager;
    /**
     * @var QuayClient
     */
    private $quayClient;

    public function __construct(
        FlowRepository $flowRepository,
        BucketRepository $bucketRepository,
        AuthenticatorClient $authenticatorClient,
        ClusterResolver $clusterResolver,
        TransactionManager $flowTransactionManager,
        QuayClient $quayClient
    ) {
        $this->flowRepository = $flowRepository;
        $this->authenticatorClient = $authenticatorClient;
        $this->bucketRepository = $bucketRepository;
        $this->clusterResolver = $clusterResolver;
        $this->flowTransactionManager = $flowTransactionManager;
        $this->quayClient = $quayClient;
    }

    public function handle(ActivateFlex $command)
    {
        $flow = $this->flowRepository->find($command->getFlowUuid());
        $bucket = $this->bucketRepository->find($flow->getTeam()->getBucketUuid());

        if (!$this->hasFlexCluster($bucket)) {
            $this->authenticatorClient->addClusterToBucket(
                $bucket->getUuid(),
                $this->clusterResolver->getCluster()
            );
        }

        try {
            $repository = $this->quayClient->createRepository('flow-' . $flow->getUuid()->toString());
        } catch (RepositoryAlreadyExists $e) {
            $repository = $e->getRepository();
        }

        $fullRegistryAddress = 'quay.io/'.$repository->getName();
        if (null === ($registry = $this->getFlexDockerRegistryCredentials($bucket, $fullRegistryAddress))) {
            $robot = $this->generateRobotAccount($flow->getTeam());
            $registry = new DockerRegistry(
                $robot->getUsername(),
                $robot->getPassword(),
                $robot->getEmail(),
                null,
                $fullRegistryAddress,
                [
                    'managed' => true,
                    'visibility' => $repository->getVisibility(),
                ]
            );

            $this->authenticatorClient->addDockerRegistryToBucket(
                $bucket->getUuid(),
                $registry
            );
        }

        $this->quayClient->allowUserToAccessRepository(
            $registry->getUsername(),
            $repository->getName()
        );

        $this->flowTransactionManager->apply($flow->getUuid()->toString(), function (Flow $flow) {
            $flow->activateFlex();
        });
    }

    private function hasFlexCluster(Bucket $bucket)
    {
        $flexClusters = $bucket->getClusters()->filter(function (Cluster $cluster) {
            return $cluster->getIdentifier() == 'flex';
        });

        return $flexClusters->count() > 0;
    }

    private function generateRobotAccount(Team $team) : RobotAccount
    {
        $robotAccountName = $this->getDockerRegistryRobotAccountName($team);

        return $this->quayClient->createRobotAccount($robotAccountName);
    }

    private function getDockerRegistryRobotAccountName(Team $team) : string
    {
        return 'project-'.$team->getSlug();
    }

    private function getFlexDockerRegistryCredentials(Bucket $bucket, string $fullAddress)
    {
        $quayCredentials = $bucket->getDockerRegistries()->filter(function (DockerRegistry $credentials) use ($fullAddress) {
            return $credentials->getFullAddress() == $fullAddress;
        });

        return !$quayCredentials->isEmpty() ? $quayCredentials->first() : null;
    }
}
