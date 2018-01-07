<?php

namespace ContinuousPipe\River\Managed\Resources\Calculation;

use ContinuousPipe\Model\Component\Resources;
use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Managed\Resources\ResourceUsage;

class ResourceCalculator
{
    /**
     * Sum resources for all environments
     *
     * @param DeployedEnvironment[] $environments
     *
     * @return ResourceUsage
     */
    public static function sumEnvironmentResources($environments = []) : ResourceUsage
    {
        $requests = new AggregateResourcesRequest();
        $limits = new AggregateResourcesRequest();

        foreach ($environments as $environment) {
            $components = $environment->getComponents();

            foreach ($components as $component) {
                if (null === ($specification = $component->getSpecification())) {
                    continue;
                }

                if (null === ($resources = $specification->getResources())) {
                    continue;
                }
            
                if (null !== ($componentRequests = $resources->getRequests())) {
                    $requests->add($componentRequests);
                }

                if (null !== ($componentLimits = $resources->getLimits())) {
                    $limits->add($componentLimits);
                }
            }
        }

        return new ResourceUsage(
            $requests->toResourcesRequest(),
            $limits->toResourcesRequest()
        );
    }
}
