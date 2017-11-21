<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\ObjectNotFound;
use Kubernetes\Client\Model\NetworkPolicy\NetworkPolicy;
use Kubernetes\Client\Repository\NetworkPolicyRepository;

class InMemoryNetworkPolicyRepository implements NetworkPolicyRepository
{
    /**
     * @var NetworkPolicy[]
     */
    private $policies = [];

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        if (!array_key_exists($name, $this->policies)) {
            throw new ObjectNotFound();
        }

        return $this->policies[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function update(NetworkPolicy $networkPolicy)
    {
        $name = $networkPolicy->getMetadata()->getName();
        if (!array_key_exists($name, $this->policies)) {
            throw new ObjectNotFound();
        }

        $this->policies[$name] = $networkPolicy;

        return $networkPolicy;
    }

    /**
     * {@inheritdoc}
     */
    public function create(NetworkPolicy $networkPolicy)
    {
        $this->policies[$networkPolicy->getMetadata()->getName()] = $networkPolicy;

        return $networkPolicy;
    }

    public function delete(NetworkPolicy $networkPolicy)
    {
        $name = $networkPolicy->getMetadata()->getName();

        if (!array_key_exists($name, $this->policies)) {
            throw new ObjectNotFound();
        }

        unset($this->policies[$name]);
    }
}
