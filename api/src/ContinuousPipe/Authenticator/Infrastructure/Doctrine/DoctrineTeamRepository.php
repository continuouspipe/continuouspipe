<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use Doctrine\ORM\EntityManager;

class DoctrineTeamRepository implements TeamRepository
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
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(Team::class);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Team $team)
    {
        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Team $team)
    {
        try {
            $this->find($team->getSlug());

            return true;
        } catch (TeamNotFound $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($slug)
    {
        if ($team = $this->getRepository()->find($slug)) {
            return $team;
        }

        throw new TeamNotFound(sprintf(
            'Team "%s" is not found'.
            $slug
        ));
    }
}
