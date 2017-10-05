<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests;

use Kubernetes\Client\Adapter\AdapterInterface;
use Kubernetes\Client\Model\KubernetesNamespace;
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
     * @var InjectedRepositoriesNamespaceClient
     */
    private $namespaceClient;

    /**
     * @param NodeRepository $nodeRepository
     * @param NamespaceRepository $namespaceRepository
     * @param InjectedRepositoriesNamespaceClient $namespaceClient
     */
    public function __construct(
        NodeRepository $nodeRepository,
        NamespaceRepository $namespaceRepository,
        InjectedRepositoriesNamespaceClient $namespaceClient
    ) {
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
        return $this->namespaceClient->withNamespace($namespace);
    }
}
