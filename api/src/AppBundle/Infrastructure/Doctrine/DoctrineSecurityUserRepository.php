<?php

namespace AppBundle\Infrastructure\Doctrine;

use AppBundle\Security\User\SecurityUserRepository;
use AppBundle\Security\User\UserNotFound;
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
