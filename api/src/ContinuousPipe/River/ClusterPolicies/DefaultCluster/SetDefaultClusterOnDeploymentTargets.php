<?php

namespace ContinuousPipe\River\ClusterPolicies\DefaultCluster;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;

class SetDefaultClusterOnDeploymentTargets implements TargetEnvironmentFactory
{
    /**
     * @var TargetEnvironmentFactory
     */
    private $decoratedFactory;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param TargetEnvironmentFactory $decoratedFactory
     * @param BucketRepository $bucketRepository
     */
    public function __construct(TargetEnvironmentFactory $decoratedFactory, BucketRepository $bucketRepository)
    {
        $this->decoratedFactory = $decoratedFactory;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, EnvironmentAwareConfiguration $configuration): Target
    {
        $target = $this->decoratedFactory->create($tide, $configuration);

        if (empty($target->getClusterIdentifier())) {
            $target = $target->withClusterIdentifier(
                $this->findDefaultClusterForTide($tide)
            );
        }

        return $target;
    }

    private function findDefaultClusterForTide(Tide $tide) : string
    {
        try {
            $clusters = $this->bucketRepository->find($tide->getTeam()->getBucketUuid())->getClusters();
        } catch (BucketNotFound $e) {
            throw new DeploymentRequestException('Cannot get team\'s credentials bucket', $e->getCode(), $e);
        }

        if ($clusters->count() === 0) {
            throw new DeploymentRequestException('You do not have any cluster to deploy to. Add a cluster in your project.');
        }

        if ($clusters->count() === 1) {
            return $clusters[0]->getIdentifier();
        }

        foreach ($clusters as $cluster) {
            if ($this->hasDefaultPolicy($cluster)) {
                return $cluster->getIdentifier();
            }
        }

        throw new DeploymentRequestException('You have multiple clusters, and no default cluster. Please set a default cluster or specify the cluster in your deployment configuration.');
    }

    private function hasDefaultPolicy(Cluster $cluster) : bool
    {
        foreach ($cluster->getPolicies() as $policy) {
            if ($policy->getName() == "default") {
                return true;
            }
        }

        return false;
    }
}
