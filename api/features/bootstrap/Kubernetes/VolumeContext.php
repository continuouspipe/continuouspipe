<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\Trace\TraceablePersistentVolumeClaimRepository;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\PersistentVolumeClaim;
use Kubernetes\Client\Model\PersistentVolumeClaimSpecification;
use Kubernetes\Client\Model\ResourceRequirements;
use Kubernetes\Client\Model\ResourceRequirementsRequests;
use Kubernetes\Client\Model\Volume;
use Kubernetes\Client\Model\VolumeMount;
use Kubernetes\Client\Repository\PodRepository;
use Kubernetes\Client\Repository\ReplicationControllerRepository;

class VolumeContext implements Context
{
    /**
     * @var TraceablePersistentVolumeClaimRepository
     */
    private $traceablePersistentVolumeClaimRepository;
    /**
     * @var ReplicationControllerRepository
     */
    private $replicationControllerRepository;
    /**
     * @var PodRepository
     */
    private $podRepository;

    /**
     * @param TraceablePersistentVolumeClaimRepository $traceablePersistentVolumeClaimRepository
     * @param ReplicationControllerRepository $replicationControllerRepository
     * @param PodRepository $podRepository
     */
    public function __construct(TraceablePersistentVolumeClaimRepository $traceablePersistentVolumeClaimRepository, ReplicationControllerRepository $replicationControllerRepository, PodRepository $podRepository)
    {
        $this->traceablePersistentVolumeClaimRepository = $traceablePersistentVolumeClaimRepository;
        $this->replicationControllerRepository = $replicationControllerRepository;
        $this->podRepository = $podRepository;
    }

    /**
     * @Given there is a volume claim :claimName
     */
    public function thereIsAVolumeClaim($claimName)
    {
        $this->traceablePersistentVolumeClaimRepository->create(new PersistentVolumeClaim(
            new ObjectMetadata($claimName),
            new PersistentVolumeClaimSpecification(
                [
                    PersistentVolumeClaimSpecification::ACCESS_MODE_READ_WRITE_MANY
                ],
                new ResourceRequirements(new ResourceRequirementsRequests('5Gi'))
            )
        ));

        $this->traceablePersistentVolumeClaimRepository->clear();
    }

    /**
     * @Then the volume claim :claimName should be created
     */
    public function theVolumeClaimShouldBeCreated($claimName)
    {
        $this->getCreatedPVCByName($claimName);
    }

    /**
     * @Then the component :componentName should be created with a persistent volume mounted in :mountPath
     */
    public function theComponentShouldBeCreatedWithAPersistentVolumeMountedIn($componentName, $mountPath)
    {
        $replicationController = $this->replicationControllerRepository->findOneByName($componentName);
        $pods = $this->podRepository->findByReplicationController($replicationController)->getPods();

        if (0 == count($pods)) {
            throw new \RuntimeException('No pods found');
        }

        foreach ($pods as $pod) {
            $container = $pod->getSpecification()->getContainers()[0];

            /** @var VolumeMount[] $matchingVolumeMounts */
            $matchingVolumeMounts = array_filter($container->getVolumeMounts(), function(VolumeMount $volumeMount) use ($mountPath) {
                return $volumeMount->getMountPath() == $mountPath;
            });

            if (0 == count($matchingVolumeMounts)) {
                throw new \RuntimeException(sprintf(
                    'No matching volume mounts found'
                ));
            }

            $volumeName = $matchingVolumeMounts[0]->getName();
            $volumes = array_filter($pod->getSpecification()->getVolumes(), function(Volume $volume) use ($volumeName) {
                return $volume->getName() == $volumeName;
            });

            if (0 === count($volumes)) {
                throw new \RuntimeException(sprintf(
                    'No volume named "%s" found',
                    $volumeName
                ));
            }

            $persistentVolumes = array_filter($volumes, function(Volume $volume) {
                return $volume->getPersistentVolumeClaim() !== null;
            });

            if (0 === count($persistentVolumes)) {
                throw new \RuntimeException('Volume found looks like not to be a persistent volume');
            }
        }
    }

    /**
     * @Then the volume claim :claimName should not be created
     */
    public function theVolumeClaimShouldNotBeCreated($claimName)
    {
        $createdClaims = $this->traceablePersistentVolumeClaimRepository->getCreated();
        $matchingClaims = array_filter($createdClaims, function(PersistentVolumeClaim $claim) use ($claimName) {
            return $claim->getMetadata()->getName() == $claimName;
        });

        if (0 != count($matchingClaims)) {
            throw new \RuntimeException(sprintf(
                'Claim named "%s" found in created claims',
                $claimName
            ));
        }
    }

    /**
     * @Then the volume claim :name should have the annotation :annotation with the value :value
     */
    public function theVolumeClaimShouldHaveTheAnnotationWithTheValue($name, $annotation, $value)
    {
        $annotations = $this->getCreatedPVCByName($name)->getMetadata()->getAnnotationsAsAssociativeArray();

        if (!array_key_exists($annotation, $annotations)) {
            throw new \RuntimeException(sprintf('Annotation "%s" not found', $annotation));
        }

        if ($value != $annotations[$annotation]) {
            throw new \RuntimeException(sprintf(
                'Found value "%s" while expecting "%s"',
                $annotations[$annotation],
                $value
            ));
        }
    }

    /**
     * @Then the volume claim :name should not have the annotation :annotation
     */
    public function theVolumeClaimShouldNotHaveTheAnnotation($name, $annotation)
    {
        if ($this->getCreatedPVCByName($name)->getMetadata()->getAnnotationList()->hasKey($annotation)) {
            throw new \RuntimeException(sprintf(
                'Found annotation "%s"',
                $annotation
            ));
        }
    }

    /**
     * @param string $claimName
     *
     * @return PersistentVolumeClaim
     */
    private function getCreatedPVCByName($claimName)
    {
        $createdClaims = $this->traceablePersistentVolumeClaimRepository->getCreated();
        $matchingClaims = array_filter($createdClaims, function (PersistentVolumeClaim $claim) use ($claimName) {
            return $claim->getMetadata()->getName() == $claimName;
        });

        if (0 == count($matchingClaims)) {
            throw new \RuntimeException(sprintf(
                'No claim named "%s" found in created claims',
                $claimName
            ));
        }

        return current($matchingClaims);
    }
}
