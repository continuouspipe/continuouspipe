<?php

namespace ContinuousPipe\River\Managed\Resources\Calculation;

use ContinuousPipe\Model\Component\Resources;
use ContinuousPipe\River\Environment\DeployedEnvironment;

class ResourceCalculator
{
    /**
     * Sum resources for all environments
     *
     * @param DeployedEnvironment[] $environments
     *
     * @return Resources
     */
    public static function sumEnvironmentResources($environments = []) : Resources
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

        return new Resources($requests->toResourcesRequest(), $limits->toResourcesRequest());
    }
}
