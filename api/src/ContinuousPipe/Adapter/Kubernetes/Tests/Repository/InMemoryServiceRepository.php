<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceList;
use Kubernetes\Client\Repository\ServiceRepository;

class InMemoryServiceRepository implements ServiceRepository
{

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function create(Service $service)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Service $service)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function update(Service $service)
    {
        throw new \RuntimeException('Not implemented yet');
    }
}