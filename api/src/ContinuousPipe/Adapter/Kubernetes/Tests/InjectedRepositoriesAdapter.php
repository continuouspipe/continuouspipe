<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests;

use Kubernetes\Client\Adapter\AdapterInterface;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\NamespaceRepository;
use Kubernetes\Client\Repository\NodeRepository;

class InjectedRepositoriesAdapter implements AdapterInterface
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var NamespaceRepository
     */
    private $namespaceRepository;

    /**
     * @var NamespaceClient
     */
    private $namespaceClient;

    /**
     * @param NodeRepository      $nodeRepository
     * @param NamespaceRepository $namespaceRepository
     * @param NamespaceClient     $namespaceClient
     */
    public function __construct(NodeRepository $nodeRepository, NamespaceRepository $namespaceRepository, NamespaceClient $namespaceClient)
    {
        $this->nodeRepository = $nodeRepository;
        $this->namespaceRepository = $namespaceRepository;
        $this->namespaceClient = $namespaceClient;
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeRepository()
    {
        return $this->nodeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaceRepository()
    {
        return $this->namespaceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaceClient(KubernetesNamespace $namespace)
    {
        return $this->namespaceClient;
    }
}
