<?php

namespace ContinuousPipe\Builder\Infrastructure\Doctrine;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use Doctrine\ORM\EntityManager;

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
        $this->entityManager->persist($build);
        $this->entityManager->flush($build);
    }
}
