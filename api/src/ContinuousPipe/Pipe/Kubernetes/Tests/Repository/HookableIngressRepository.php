<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository;

use GuzzleHttp\Promise\PromiseInterface;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\IngressList;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Repository\IngressRepository;

class HookableIngressRepository implements IngressRepository
{
    /**
     * @var callable[]
     */
    private $findOneByNameHooks = [];

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
        return $this->decoratedRepository->asyncFindAll()->then(function (IngressList $ingressList) {
            return IngressList::fromIngresses(array_map(function (Ingress $ingress) {
                return $this->applyHooks($ingress);
            }, $ingressList->getIngresses()));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        $ingress = $this->decoratedRepository->findOneByName($name);
        $ingress = $this->applyHooks($ingress);

        return $ingress;
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        $found = $this->decoratedRepository->findByLabels($labels);

        return IngressList::fromIngresses(array_map(function (Ingress $ingress) {
            return $this->applyHooks($ingress);
        }, $found->getIngresses()));
    }

    /**
     * {@inheritdoc}
     */
    public function create(Ingress $ingress)
    {
        return $this->decoratedRepository->create($ingress);
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
     * {@inheritdoc}
     */
    public function annotate(string $name, KeyValueObjectList $annotations)
    {
        return $this->decoratedRepository->annotate($name, $annotations);
    }

    /**
     * @param callable $hook
     */
    public function addFindOneByNameHooks(callable $hook)
    {
        $this->findOneByNameHooks[] = $hook;
    }

    /**
     * @param Ingress $ingress
     *
     * @return Ingress
     */
    private function applyHooks(Ingress $ingress)
    {
        foreach ($this->findOneByNameHooks as $hook) {
            $ingress = $hook($ingress);
        }

        return $ingress;
    }
}
