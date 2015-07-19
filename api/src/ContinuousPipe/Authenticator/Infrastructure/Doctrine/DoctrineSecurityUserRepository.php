<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use Doctrine\ORM\EntityManager;
use ContinuousPipe\User\SecurityUser;

class DoctrineSecurityUserRepository implements SecurityUserRepository
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
     * {@inheritdoc.
     */
    public function findOneByEmail($email)
    {
        $user = $this->entityManager->getRepository(SecurityUser::class)->findOneBy([
            'username' => $email,
        ]);

        if (null === $user) {
            throw new UserNotFound();
        }

        return $user;
    }

    /**
     * {@inheritdoc.
     */
    public function save(SecurityUser $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }
}
