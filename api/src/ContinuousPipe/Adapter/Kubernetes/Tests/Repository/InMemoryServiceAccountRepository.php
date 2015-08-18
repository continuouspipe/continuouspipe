<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\ServiceAccountNotFound;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\ServiceAccount;
use Kubernetes\Client\Repository\ServiceAccountRepository;

class InMemoryServiceAccountRepository implements ServiceAccountRepository
{
    /**
     * @var ServiceAccount[]
     */
    private $serviceAccounts = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->serviceAccounts = [
            'default' => new ServiceAccount(new ObjectMetadata('default'), [], []),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        if (!array_key_exists($name, $this->serviceAccounts)) {
            throw new ServiceAccountNotFound();
        }

        return $this->serviceAccounts[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function update(ServiceAccount $serviceAccount)
    {
        $name = $serviceAccount->getMetadata()->getName();
        if (!array_key_exists($name, $this->serviceAccounts)) {
            throw new ServiceAccountNotFound();
        }

        $this->serviceAccounts[$name] = $serviceAccount;

        return $serviceAccount;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ServiceAccount $serviceAccount)
    {
        $this->serviceAccounts[$serviceAccount->getMetadata()->getName()] = $serviceAccount;

        return $serviceAccount;
    }
}
