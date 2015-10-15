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
        $whiteListedUser = $this->getRepository()->findOneBy([
            'gitHubUsername' => $username,
        ]);

        return null !== $whiteListedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function add($username)
    {
        $this->entityManager->persist(new WhiteListedUser($username));
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($username)
    {
        $whiteListedUser = $this->getRepository()->findOneBy(['gitHubUsername' => $username]);

        $this->entityManager->remove($whiteListedUser);
        $this->entityManager->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(WhiteListedUser::class);
    }
}
