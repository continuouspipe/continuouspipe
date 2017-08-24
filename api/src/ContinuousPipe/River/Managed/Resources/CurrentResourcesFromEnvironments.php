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

/**
 * This will be replaced by the resource history. The history is sent by the cluster themselves, through
 * the kube-resources-watcher
 *
 * @see https://github.com/continuouspipe/continuouspipe/tree/master/kube-resources-watcher
 */
class CurrentResourcesFromEnvironments implements ResourceUsageResolver
{
    /**
     * @var DeployedEnvironmentRepository
     */
    private $deployedEnvironmentRepository;

    /**
     * @param DeployedEnvironmentRepository $deployedEnvironmentRepository
     */
    public function __construct(DeployedEnvironmentRepository $deployedEnvironmentRepository)
    {
        $this->deployedEnvironmentRepository = $deployedEnvironmentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function forFlow(FlatFlow $flow): ResourceUsage
    {
        try {
            return ResourceCalculator::sumEnvironmentResources(
                $this->deployedEnvironmentRepository->findByFlow($flow)
            );
        } catch (DeployedEnvironmentException $e) {
            throw new ResourcesException('Can\'t get environments for the flow', $e->getCode(), $e);
        }
    }
}
