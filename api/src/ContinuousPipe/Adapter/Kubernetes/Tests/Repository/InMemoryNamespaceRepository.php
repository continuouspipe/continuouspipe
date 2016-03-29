<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\NamespaceList;
use Kubernetes\Client\Repository\NamespaceRepository;

class InMemoryNamespaceRepository implements NamespaceRepository
{
    /**
     * @var KubernetesNamespace[]
     */
    private $namespaces = [];

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return new NamespaceList($this->namespaces);
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(KeyValueObjectList $labels)
    {
        return new NamespaceList(array_values(array_filter($this->namespaces, function (KubernetesNamespace $namespace) use ($labels) {
            $foundLabels = $namespace->getMetadata()->getLabelsAsAssociativeArray();

            foreach ($labels->toAssociativeArray() as $key => $value) {
                if (!array_key_exists($key, $foundLabels)) {
                    return false;
                } elseif ($foundLabels[$key] != $value) {
                    return false;
                }
            }

            return true;
        })));
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->namespaces)) {
            throw new NamespaceNotFound();
        }

        return $this->namespaces[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->namespaces);
    }

    /**
     * {@inheritdoc}
     */
    public function create(KubernetesNamespace $namespace)
    {
        $this->namespaces[$namespace->getMetadata()->getName()] = $namespace;

        return $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(KubernetesNamespace $namespace)
    {
        $namespaceName = $namespace->getMetadata()->getName();

        if (!$this->exists($namespaceName)) {
            unset($this->namespaces[$namespaceName]);
        }

        return $namespace;
    }
}
