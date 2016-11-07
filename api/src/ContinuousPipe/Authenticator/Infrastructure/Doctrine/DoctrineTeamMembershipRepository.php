<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class DoctrineTeamMembershipRepository implements TeamMembershipRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EntityManager   $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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
        try {
            $this->entityManager->persist($membership);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->warning('Team membership already saved, ignoring.', [
                'username' => $membership->getUser()->getUsername(),
                'team' => $membership->getTeam()->getSlug(),
                'permissions' => $membership->getPermissions(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(TeamMembership $membership)
    {
        $memberships = $this->getRepository()->findBy([
            'user' => $membership->getUser(),
            'team' => $membership->getTeam(),
        ]);

        foreach ($memberships as $membership) {
            $this->entityManager->remove($membership);
        }

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
