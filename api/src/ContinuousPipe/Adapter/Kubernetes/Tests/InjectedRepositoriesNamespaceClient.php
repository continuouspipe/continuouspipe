<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests;

use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\PodRepository;
use Kubernetes\Client\Repository\ReplicationControllerRepository;
use Kubernetes\Client\Repository\ServiceRepository;

class InjectedRepositoriesNamespaceClient implements NamespaceClient
{
    /**
     * @var PodRepository
     */
    private $podRepository;

    /**
     * @var ServiceRepository
     */
    private $serviceRepository;

    /**
     * @var ReplicationControllerRepository
     */
    private $replicationControllerRepository;

    /**
     * @param PodRepository $podRepository
     * @param ServiceRepository $serviceRepository
     * @param ReplicationControllerRepository $replicationControllerRepository
     */
    public function __construct(PodRepository $podRepository, ServiceRepository $serviceRepository, ReplicationControllerRepository $replicationControllerRepository)
    {
        $this->podRepository = $podRepository;
        $this->serviceRepository = $serviceRepository;
        $this->replicationControllerRepository = $replicationControllerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getPodRepository()
    {
        return $this->podRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceRepository()
    {
        return $this->serviceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getReplicationControllerRepository()
    {
        return $this->replicationControllerRepository;
    }
}