<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\BeforeCreatingComponent;

use ContinuousPipe\Adapter\Kubernetes\Event\BeforeCreatingComponent;
use ContinuousPipe\Model\Component\Volume;
use Kubernetes\Client\Exception\PersistentVolumeClaimNotFound;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\PersistentVolumeClaim;
use Kubernetes\Client\Model\PersistentVolumeClaimSpecification;
use Kubernetes\Client\Model\ResourceRequirements;
use Kubernetes\Client\Model\ResourceRequirementsRequests;
use Kubernetes\Client\NamespaceClient;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreatePersistentVolumeClaim implements EventSubscriberInterface
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeCreatingComponent::NAME => 'listen',
        ];
    }

    /**
     * @param BeforeCreatingComponent $event
     */
    public function listen(BeforeCreatingComponent $event)
    {
        $componentVolumes = $event->getComponent()->getSpecification()->getVolumes();
        $persistentVolumes = array_filter($componentVolumes, function (Volume $volume) {
            return $volume instanceof Volume\Persistent;
        });

        if (0 === count($persistentVolumes)) {
            return;
        }

        $logger = $this->loggerFactory->from($event->getContext()->getLog());
        foreach ($persistentVolumes as $persistentVolume) {
            $this->createPersistentVolumeClaimIfNotExists($event->getClient(), $logger, $persistentVolume);
        }
    }

    /**
     * Create the asked persistent volume claim if it do not exists yet.
     *
     * @param NamespaceClient   $client
     * @param Volume\Persistent $persistentVolume
     */
    private function createPersistentVolumeClaimIfNotExists(NamespaceClient $client, Logger $logger, Volume\Persistent $persistentVolume)
    {
        $persistentVolumeClaimRepository = $client->getPersistentVolumeClaimRepository();

        try {
            $persistentVolumeClaimRepository->findOneByName($persistentVolume->getName());
            $logger->child(new Text(sprintf(
                'Reusing existing persistent volume claim "%s"',
                $persistentVolume->getName()
            )));
        } catch (PersistentVolumeClaimNotFound $e) {
            $created = $persistentVolumeClaimRepository->create(new PersistentVolumeClaim(
                new ObjectMetadata($persistentVolume->getName()),
                new PersistentVolumeClaimSpecification(
                    [PersistentVolumeClaimSpecification::ACCESS_MODE_READ_WRITE_ONCE],
                    new ResourceRequirements(new ResourceRequirementsRequests(
                        $persistentVolume->getCapacity()
                    ))
                )
            ));

            $logger->child(new Text(sprintf(
                'Created persistent volume claim "%s"',
                $created->getMetadata()->getName()
            )));
        }
    }
}
