<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\User\WhiteList;
use ContinuousPipe\User\WhiteListedUser;
use Doctrine\ORM\EntityManager;

class DoctrineWhiteList implements WhiteList
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
    public function contains($username)
    {
        $user = $this->entityManager->getRepository(WhiteListedUser::class)
            ->findOneBy([
                'gitHubUsername' => $username,
            ]);

        return null !== $user;
    }
}
