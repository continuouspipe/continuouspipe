<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository\Trace\RBAC;

use Kubernetes\Client\Exception\Exception;
use Kubernetes\Client\Exception\ObjectNotFound;
use Kubernetes\Client\Model\RBAC\RoleBinding;
use Kubernetes\Client\Repository\RBAC\RoleBindingRepository;

class TraceableRoleBindingRepository implements RoleBindingRepository
{
    /**
     * @var RoleBindingRepository
     */
    private $decoratedRepository;

    /**
     * @var RoleBinding[]
     */
    private $created = [];

    /**
     * @param RoleBindingRepository $decoratedRepository
     */
    public function __construct(RoleBindingRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        return $this->decoratedRepository->findOneByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function create(RoleBinding $binding)
    {
        $binding = $this->decoratedRepository->create($binding);

        $this->created[] = $binding;

        return $binding;
    }

    /**
     * @return RoleBinding[]
     */
    public function getCreated(): array
    {
        return $this->created;
    }
}
