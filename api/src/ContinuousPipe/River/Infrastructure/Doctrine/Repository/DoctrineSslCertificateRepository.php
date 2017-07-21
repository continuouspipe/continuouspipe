<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository;

use ContinuousPipe\Model\Component\Endpoint\SslCertificate;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\SslCertificateStorage;
use ContinuousPipe\River\SslCertificate\SslCertificateRepository;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;

class DoctrineSslCertificateRepository implements SslCertificateRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $flowUuid, string $hostname)
    {
        $certificate = $this->entityManager->getRepository(SslCertificateStorage::class)->findOneBy([
            'flowUuid' => $flowUuid,
            'hostname' => $hostname,
        ]);

        if (null !== $certificate) {
            $certificate = new SslCertificate(
                $certificate->name,
                $certificate->cert,
                $certificate->key
            );
        }

        return $certificate;
    }

    /**
     * {@inheritdoc}
     */
    public function save(UuidInterface $flowUuid, string $hostname, SslCertificate $certificate)
    {
        $storage = new SslCertificateStorage();
        $storage->flowUuid = $flowUuid;
        $storage->hostname = $hostname;
        $storage->name = $certificate->getName();
        $storage->cert = $certificate->getCert();
        $storage->key = $certificate->getKey();

        $storage = $this->entityManager->merge($storage);

        $this->entityManager->persist($storage);
        $this->entityManager->flush();
    }
}
