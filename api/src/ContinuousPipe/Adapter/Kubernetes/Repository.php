<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Adapter\ProviderRepository;
use Doctrine\ORM\EntityManager;

class Repository implements ProviderRepository
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
    public function create(Provider $provider)
    {
        $this->entityManager->persist($provider);
        $this->entityManager->flush();

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier)
    {
        return $this->getRepository()->find($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Provider $provider)
    {
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository('ContinuousPipe\Adapter\Kubernetes\KubernetesProvider');
    }
}
