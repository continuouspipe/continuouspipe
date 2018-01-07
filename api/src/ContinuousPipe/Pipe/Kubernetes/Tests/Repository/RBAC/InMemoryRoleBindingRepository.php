<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository\RBAC;

use Kubernetes\Client\Exception\Exception;
use Kubernetes\Client\Exception\ObjectNotFound;
use Kubernetes\Client\Model\RBAC\RoleBinding;
use Kubernetes\Client\Repository\RBAC\RoleBindingRepository;

class InMemoryRoleBindingRepository implements RoleBindingRepository
{
    /**
     * @var RoleBinding[]
     */
    private $bindings = [];

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->bindings)) {
            throw new ObjectNotFound(sprintf(
                'Role binding "%s" not found',
                $name
            ));
        }

        return $this->bindings[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function create(RoleBinding $binding)
    {
        return $this->bindings[$binding->getMetadata()->getName()] = $binding;
    }
}
