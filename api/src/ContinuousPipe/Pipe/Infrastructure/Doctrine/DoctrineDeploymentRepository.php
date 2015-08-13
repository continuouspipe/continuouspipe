<?php

namespace ContinuousPipe\Pipe\Infrastructure\Doctrine;

use ContinuousPipe\Pipe\Deployment;
use ContinuousPipe\Pipe\DeploymentRepository;
use Doctrine\ORM\EntityManager;
use Rhumsaa\Uuid\Uuid;

class DoctrineDeploymentRepository implements DeploymentRepository
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
    public function find(Uuid $uuid)
    {
        return $this->getRepository()->find((string) $uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Deployment $deployment)
    {
        $this->entityManager->persist($deployment);
        $this->entityManager->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(Deployment::class);
    }
}
