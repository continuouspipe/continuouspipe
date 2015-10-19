<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Security\User\SecurityUser;
use Doctrine\ORM\EntityManager;

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
    public function findOneByUsername($username)
    {
        $user = $this->entityManager->getRepository(SecurityUser::class)->findOneBy([
            'username' => $username,
        ]);

        if (null === $user) {
            throw new UserNotFound(sprintf(
                'User "%s" is not found',
                $username
            ));
        }

        return $user;
    }

    /**
     * {@inheritdoc.
     */
    public function save(SecurityUser $user)
    {
        $this->entityManager->merge($user);
        $this->entityManager->flush();

        return $user;
    }
}
