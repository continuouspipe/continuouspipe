<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceList;
use Kubernetes\Client\Repository\ServiceRepository;

class HookableServiceRepository implements ServiceRepository
{
    /**
     * @var callable[]
     */
    private $findOneByNameHooks = [];

    /**
     * @var ServiceRepository
     */
    private $repository;

    /**
     * @param ServiceRepository $repository
     */
    public function __construct(ServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        $service = $this->repository->findOneByName($name);

        foreach ($this->findOneByNameHooks as $hook) {
            $service = $hook($service);
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        return ServiceList::fromServices(array_map(function (Service $service) {
            foreach ($this->findOneByNameHooks as $hook) {
                $service = $hook($service);
            }

            return $service;
        }, $this->repository->findByLabels($labels)->getServices()));
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->repository->exists($name);
    }

    /**
     *{@inheritdoc}
     */
    public function create(Service $service)
    {
        return $this->repository->create($service);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Service $service)
    {
        return $this->repository->delete($service);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Service $service)
    {
        return $this->repository->update($service);
    }

    /**
     * @param callable $hook
     */
    public function addFindOneByNameHooks(callable $hook)
    {
        $this->findOneByNameHooks[] = $hook;
    }
}
