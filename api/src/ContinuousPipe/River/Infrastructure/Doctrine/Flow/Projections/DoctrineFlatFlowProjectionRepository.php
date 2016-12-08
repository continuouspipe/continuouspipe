<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Flow\Projections;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\Security\Team\Team;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;

class DoctrineFlatFlowProjectionRepository implements FlatFlowRepository
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
    public function find(UuidInterface $uuid)
    {
        if (null === ($flow = $this->getRepository()->find((string) $uuid))) {
            throw new FlowNotFound(sprintf('Flow "%s" not found', (string) $uuid));
        }

        return $flow;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team) : array
    {
        return $this->getRepository()->findBy([
            'team' => $team,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $repository) : array
    {
        return $this->getRepository()->findBy([
            'repositroy' => $repository,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(UuidInterface $uuid)
    {
        $flow = $this->find($uuid);

        $this->entityManager->remove($flow);
        $this->entityManager->flush($flow);
    }

    /**
     * {@inheritdoc}
     */
    public function save(FlatFlow $flow)
    {
        $this->entityManager->persist($flow);
        $this->entityManager->flush($flow);
    }

    private function getRepository()
    {
        return $this->entityManager->getRepository(FlatFlow::class);
    }
}
