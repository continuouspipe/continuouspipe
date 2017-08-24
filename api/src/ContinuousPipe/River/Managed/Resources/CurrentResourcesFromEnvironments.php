<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\Model\Component\Resources;
use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Environment\DeployedEnvironmentException;
use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Managed\Resources\Calculation\ResourceCalculator;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;

class CurrentResourcesFromEnvironments implements ResourceUsageResolver
{
    /**
     * @var DeployedEnvironmentRepository
     */
    private $deployedEnvironmentRepository;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param DeployedEnvironmentRepository $deployedEnvironmentRepository
     * @param BucketRepository $bucketRepository
     */
    public function __construct(DeployedEnvironmentRepository $deployedEnvironmentRepository, BucketRepository $bucketRepository)
    {
        $this->deployedEnvironmentRepository = $deployedEnvironmentRepository;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function forFlow(FlatFlow $flow): ResourceUsage
    {
        try {
            $clusters = $this->bucketRepository->find($flow->getTeam()->getBucketUuid())->getClusters();
        } catch (BucketNotFound $e) {
            throw new ResourcesException('Can\'t get bucket from flow', $e->getCode(), $e);
        }

        try {
            $clusterUsages = \GuzzleHttp\Promise\unwrap($clusters->map(function (Cluster $cluster) use ($flow) {
                return $this->deployedEnvironmentRepository->findByFlowAndCluster($flow, $cluster)->then(function (array $deployedEnvironments) use ($cluster) {
                    return new ClusterResourceUsage(
                        $cluster->getIdentifier(),
                        ResourceCalculator::sumEnvironmentResources($deployedEnvironments)
                    );
                });
            }));
        } catch (DeployedEnvironmentException $e) {
            throw new ResourcesException('Can\'t get environments for the flow', $e->getCode(), $e);
        }

        return new ResourceUsage($clusterUsages);
    }
}
