<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\PersistentVolumeClaimNotFound;
use Kubernetes\Client\Model\PersistentVolumeClaim;
use Kubernetes\Client\Repository\PersistentVolumeClaimRepository;

class InMemoryPersistentVolumeClaimRepository implements PersistentVolumeClaimRepository
{
    /**
     * @var array
     */
    private $persistentVolumeClaims = [];

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->persistentVolumeClaims)) {
            throw new PersistentVolumeClaimNotFound(sprintf(
                'PVC "%s" not found',
                $name
            ));
        }

        return $this->persistentVolumeClaims[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function create(PersistentVolumeClaim $claim)
    {
        $this->persistentVolumeClaims[$claim->getMetadata()->getName()] = $claim;

        return $claim;
    }
}
