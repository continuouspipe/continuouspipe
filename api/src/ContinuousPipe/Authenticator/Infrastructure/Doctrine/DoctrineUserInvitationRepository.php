<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use ContinuousPipe\Security\Team\Team;
use Doctrine\ORM\EntityManager;

class DoctrineUserInvitationRepository implements UserInvitationRepository
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
    public function findByUserEmail($email)
    {
        return $this->getRepository()->findBy([
            'userEmail' => $email,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        return $this->getRepository()->findBy([
            'teamSlug' => $team->getSlug(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserInvitation $userInvitation)
    {
        $this->entityManager->persist($userInvitation);
        $this->entityManager->flush($userInvitation);

        return $userInvitation;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UserInvitation $invitation)
    {
        $this->entityManager->remove($invitation);
        $this->entityManager->flush();
    }

    /**
     * @return UserInvitationRepository|\Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(UserInvitation::class);
    }
}
