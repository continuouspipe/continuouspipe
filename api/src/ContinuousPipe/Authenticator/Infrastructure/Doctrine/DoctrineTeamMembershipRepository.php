<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class DoctrineTeamMembershipRepository implements TeamMembershipRepository
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
    public function findByUser(User $user)
    {
        $memberships = $this->getRepository()->findBy([
            'user' => $user,
        ]);

        return new ArrayCollection($memberships);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        $memberships = $this->getRepository()->findBy([
            'team' => $team,
        ]);

        return new ArrayCollection($memberships);
    }

    /**
     * {@inheritdoc}
     */
    public function save(TeamMembership $membership)
    {
        $this->entityManager->persist($membership);
        $this->entityManager->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(TeamMembership::class);
    }
}
