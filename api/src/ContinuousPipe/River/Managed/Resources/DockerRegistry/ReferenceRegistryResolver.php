<?php

namespace ContinuousPipe\River\Managed\Resources\DockerRegistry;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use Ramsey\Uuid\UuidInterface;

class ReferenceRegistryResolver
{
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    public function __construct(FlatFlowRepository $flatFlowRepository, BucketRepository $bucketRepository)
    {
        $this->flatFlowRepository = $flatFlowRepository;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @param UuidInterface $flowUuid
     *
     * @throws FlowNotFound
     *
     * @return DockerRegistry|null
     */
    public function getReferenceRegistry(UuidInterface $flowUuid)
    {
        $bucketUuid = $this->flatFlowRepository->find($flowUuid)->getTeam()->getBucketUuid();

        try {
            $registries = $this->bucketRepository->find($bucketUuid)->getDockerRegistries();
        } catch (BucketNotFound $e) {
            return null;
        }

        // Find a registry matching the flow
        foreach ($registries as $registry) {
            if (isset($registry->getAttributes()['flow']) && $registry->getAttributes()['flow'] == $flowUuid->toString()) {
                return $registry;
            }
        }

        if ($registries->count() > 0) {
            return $registries->first();
        }

        return null;
    }
}
