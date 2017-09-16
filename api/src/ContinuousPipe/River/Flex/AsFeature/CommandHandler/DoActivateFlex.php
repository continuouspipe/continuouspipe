<?php

namespace ContinuousPipe\River\Flex\AsFeature\CommandHandler;

use ContinuousPipe\Events\Transaction\TransactionManager;
use ContinuousPipe\QuayIo\QuayClient;
use ContinuousPipe\QuayIo\RepositoryAlreadyExists;
use ContinuousPipe\QuayIo\RobotAccount;
use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flex\Cluster\ClusterResolver;
use ContinuousPipe\River\Flex\Resources\DockerRegistry\DockerRegistryManager;
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
     * @var DockerRegistryManager
     */
    private $dockerRegistryManager;

    public function __construct(
        FlowRepository $flowRepository,
        AuthenticatorClient $authenticatorClient,
        ClusterResolver $clusterResolver,
        TransactionManager $flowTransactionManager,
        BucketRepository $bucketRepository,
        DockerRegistryManager $dockerRegistryManager
    ) {
        $this->flowRepository = $flowRepository;
        $this->bucketRepository = $bucketRepository;
        $this->clusterResolver = $clusterResolver;
        $this->flowTransactionManager = $flowTransactionManager;
        $this->authenticatorClient = $authenticatorClient;
        $this->dockerRegistryManager = $dockerRegistryManager;
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

        $this->dockerRegistryManager->createRepositoryForFlow($flow);
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
}
