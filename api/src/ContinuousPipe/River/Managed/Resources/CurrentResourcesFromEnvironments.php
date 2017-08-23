<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\Model\Component\Resources;
use ContinuousPipe\River\Environment\DeployedEnvironmentException;
use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Managed\Resources\Calculation\ResourceCalculator;

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
    public function forFlow(FlatFlow $flow): Resources
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
