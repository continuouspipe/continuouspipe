<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository\Trace;

use GuzzleHttp\Promise\PromiseInterface;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Repository\IngressRepository;

class TraceableIngressRepository implements IngressRepository
{
    /**
     * @var Ingress[]
     */
    private $created = [];

    /**
     * @var Ingress[]
     */
    private $updated = [];

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
    public function asyncFindAll() : PromiseInterface
    {
        return $this->decoratedRepository->asyncFindAll();
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
        $updated = $this->decoratedRepository->update($ingress);

        $this->updated[] = $updated;

        return $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function annotate(string $name, KeyValueObjectList $annotations)
    {
        $annotated = $this->decoratedRepository->annotate($name, $annotations);

        $this->updated[] = $annotated;

        return $annotated;
    }

    /**
     * @return Ingress[]
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return Ingress[]
     */
    public function getUpdated(): array
    {
        return $this->updated;
    }
}
