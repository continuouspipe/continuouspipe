<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceList;
use Kubernetes\Client\Repository\ServiceRepository;

class InMemoryServiceRepository implements ServiceRepository
{
    /**
     * @var Service[]
     */
    private $services = [];

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return ServiceList::fromServices($this->services);
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        $services = array_values(array_filter($this->services, function (Service $service) use ($labels) {
            $serviceLabels = $service->getMetadata()->getLabelsAsAssociativeArray();

            foreach ($labels as $key => $value) {
                if (!array_key_exists($key, $serviceLabels) || $serviceLabels[$key] != $value) {
                    return false;
                }
            }

            return true;
        }));

        return ServiceList::fromServices($services);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->services)) {
            throw new ServiceNotFound();
        }

        return $this->services[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->services);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Service $service)
    {
        $this->services[$service->getMetadata()->getName()] = $service;

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Service $service)
    {
        $name = $service->getMetadata()->getName();
        if (!array_key_exists($name, $this->services)) {
            throw new ServiceNotFound();
        }

        unset($this->services[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Service $service)
    {
        $this->services[$service->getMetadata()->getName()] = $service;

        return $service;
    }
}
