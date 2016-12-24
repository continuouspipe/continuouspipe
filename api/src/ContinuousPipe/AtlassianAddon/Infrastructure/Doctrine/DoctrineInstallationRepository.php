<?php

namespace ContinuousPipe\AtlassianAddon\Infrastructure\Doctrine;

use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\InstallationRepository;
use Doctrine\ORM\EntityManager;

class DoctrineInstallationRepository implements InstallationRepository
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

    public function save(Installation $installation)
    {
        $this->entityManager->persist($installation);
        $this->entityManager->flush();
    }

    public function findByPrincipal(string $type, string $username) : array
    {
        return $this->entityManager->getRepository(Installation::class)->findBy([
            'principal.type' => $type,
            'principal.username' => $username,
        ]);
    }
}
