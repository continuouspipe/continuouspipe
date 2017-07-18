<?php

namespace ContinuousPipe\River\Flex\AsFeature\CommandHandler;

use ContinuousPipe\Events\Transaction\TransactionManager;
use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flex\Cluster\ClusterResolver;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;

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

    public function __construct(
        FlowRepository $flowRepository,
        BucketRepository $bucketRepository,
        AuthenticatorClient $authenticatorClient,
        ClusterResolver $clusterResolver,
        TransactionManager $flowTransactionManager
    ) {
        $this->flowRepository = $flowRepository;
        $this->authenticatorClient = $authenticatorClient;
        $this->bucketRepository = $bucketRepository;
        $this->clusterResolver = $clusterResolver;
        $this->flowTransactionManager = $flowTransactionManager;
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
