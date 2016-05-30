<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\IngressNotFound;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Repository\IngressRepository;

class InMemoryIngressRepository implements IngressRepository
{
    private $ingresses = [];

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->ingresses)) {
            throw new IngressNotFound(sprintf('Ingress "%s" not found', $name));
        }

        return $this->ingresses[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function create(Ingress $ingress)
    {
        $this->ingresses[$ingress->getMetadata()->getName()] = $ingress;

        return $ingress;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->ingresses);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Ingress $ingress)
    {
        $this->ingresses[$ingress->getMetadata()->getName()] = $ingress;
    }
}
