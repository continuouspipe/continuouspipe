<?php

namespace ContinuousPipe\Builder\Infrastructure\Doctrine;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildNotFound;
use ContinuousPipe\Builder\BuildRepository;
use Doctrine\ORM\EntityManager;
use Rhumsaa\Uuid\Uuid;

class DoctrineBuildRepository implements BuildRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Build $build)
    {
        $build = $this->entityManager->merge($build);

        $this->entityManager->persist($build);
        $this->entityManager->flush($build);

        return $build;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        $build = $this->entityManager->getRepository('ContinuousPipeBuilder:Build')->find((string) $uuid);
        if (null === $build) {
            throw new BuildNotFound();
        }

        return $build;
    }
}
