<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\ServiceAccount;
use Kubernetes\Client\Repository\ServiceAccountRepository;

class HookableServiceAccountRepository implements ServiceAccountRepository
{
    /**
     * @var ServiceAccountRepository
     */
    private $decoratedRepository;

    /**
     * @var callable[]
     */
    private $findByNameHooks = [];

    /**
     * @param ServiceAccountRepository $decoratedRepository
     */
    public function __construct(ServiceAccountRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        $serviceAccount = $this->decoratedRepository->findByName($name);

        foreach ($this->findByNameHooks as $hook) {
            $serviceAccount = $hook($serviceAccount);
        }

        return $serviceAccount;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ServiceAccount $serviceAccount)
    {
        return $this->decoratedRepository->update($serviceAccount);
    }

    /**
     * {@inheritdoc}
     */
    public function create(ServiceAccount $serviceAccount)
    {
        return $this->decoratedRepository->create($serviceAccount);
    }

    /**
     * @param callable $hook
     */
    public function addFindByNameHook(callable $hook)
    {
        $this->findByNameHooks[] = $hook;
    }
}
