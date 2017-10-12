<?php

namespace ContinuousPipe\River\Analytics\Keen\Normalizer;

use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\View\ComponentStatus;
use ContinuousPipe\Pipe\View\Deployment;

class DeploymentNormalizer
{
    public function normalize(Deployment $deployment)
    {
        $request = $deployment->getRequest();

        return [
            'uuid' => (string) $deployment->getUuid(),
            'status' => $deployment->getStatus(),
            'target' => [
                'cluster' => $request->getTarget()->getClusterIdentifier(),
                'environment_name' => $request->getTarget()->getEnvironmentName(),
            ],
            'component_statuses' => array_map(function (ComponentStatus $componentStatus) {
                return [
                    'created' => $componentStatus->isCreated(),
                    'updated' => $componentStatus->isUpdated(),
                    'deleted' => $componentStatus->isDeleted(),
                ];
            }, $deployment->getComponentStatuses()),
            'public_endpoints' => array_map(function (PublicEndpoint $endpoint) {
                return [
                    'name' => $endpoint->getName(),
                    'address' => $endpoint->getAddress(),
                ];
            }, $deployment->getPublicEndpoints()),
        ];
    }
}
