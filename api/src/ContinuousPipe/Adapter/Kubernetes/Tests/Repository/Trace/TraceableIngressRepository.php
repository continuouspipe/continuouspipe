<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Repository\IngressRepository;

class TraceableIngressRepository implements IngressRepository
{
    /**
     * @var Ingress[]
     */
    private $created = [];

    /**
     * @var IngressRepository
     */
    private $decoratedRepository;

    /**
     * @param IngressRepository $decoratedRepository
     */
    public function __construct(IngressRepository $decoratedRepository)
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
    public function findByLabels(array $labels)
    {
        return $this->decoratedRepository->findByLabels($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Ingress $ingress)
    {
        $created = $this->decoratedRepository->create($ingress);

        $this->created[] = $created;

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->decoratedRepository->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Ingress $ingress)
    {
        return $this->decoratedRepository->update($ingress);
    }

    /**
     * @return Ingress[]
     */
    public function getCreated()
    {
        return $this->created;
    }
}
