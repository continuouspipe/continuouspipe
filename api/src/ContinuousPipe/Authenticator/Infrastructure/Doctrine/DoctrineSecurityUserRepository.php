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
     * {@inheritdoc}
     */
    public function findOneByEmail($email)
    {
        $user = $this->entityManager->getRepository(SecurityUser::class)->createQueryBuilder('security_user')
            ->join('security_user.user', 'user')
            ->where('user.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $user) {
            throw new UserNotFound(sprintf(
                'User with email "%s" is not found',
                $email
            ));
        }

        return $user;
    }

    /**
     * {@inheritdoc.
     */
    public function save(SecurityUser $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('count(u.username)');
        $queryBuilder->from(SecurityUser::class, 'u');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
