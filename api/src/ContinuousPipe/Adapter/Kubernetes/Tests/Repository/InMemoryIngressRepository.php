<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\IngressNotFound;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\IngressList;
use Kubernetes\Client\Repository\IngressRepository;

class InMemoryIngressRepository implements IngressRepository
{
    /**
     * @var Ingress[]
     */
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

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        $ingresses = array_values(array_filter($this->ingresses, function (Ingress $ingress) use ($labels) {
            $ingressLabels = $ingress->getMetadata()->getLabelsAsAssociativeArray();

            foreach ($labels as $key => $value) {
                if (!array_key_exists($key, $ingressLabels) || $ingressLabels[$key] != $value) {
                    return false;
                }
            }

            return true;
        }));

        return IngressList::fromIngresses($ingresses);
    }
}
