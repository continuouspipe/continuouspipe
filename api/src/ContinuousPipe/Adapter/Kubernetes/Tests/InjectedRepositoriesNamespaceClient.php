<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests;

use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\DeploymentRepository;
use Kubernetes\Client\Repository\EventRepository;
use Kubernetes\Client\Repository\IngressRepository;
use Kubernetes\Client\Repository\PersistentVolumeClaimRepository;
use Kubernetes\Client\Repository\PodRepository;
use Kubernetes\Client\Repository\RBAC\RoleBindingRepository;
use Kubernetes\Client\Repository\ReplicationControllerRepository;
use Kubernetes\Client\Repository\SecretRepository;
use Kubernetes\Client\Repository\ServiceAccountRepository;
use Kubernetes\Client\Repository\ServiceRepository;

class InjectedRepositoriesNamespaceClient implements NamespaceClient
{
    /**
     * @var KubernetesNamespace|null
     */
    private $namespace;

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
     * @var SecretRepository
     */
    private $secretRepository;

    /**
     * @var ServiceAccountRepository
     */
    private $serviceAccountRepository;

    /**
     * @var PersistentVolumeClaimRepository
     */
    private $persistentVolumeClaimRepository;

    /**
     * @var IngressRepository
     */
    private $ingressRepository;

    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var RoleBindingRepository
     */
    private $roleBindingRepository;

    public function __construct(
        PodRepository $podRepository,
        ServiceRepository $serviceRepository,
        ReplicationControllerRepository $replicationControllerRepository,
        SecretRepository $secretRepository,
        ServiceAccountRepository $serviceAccountRepository,
        PersistentVolumeClaimRepository $persistentVolumeClaimRepository,
        IngressRepository $ingressRepository,
        DeploymentRepository $deploymentRepository,
        EventRepository $eventRepository,
        RoleBindingRepository $roleBindingRepository
    ) {
        $this->podRepository = $podRepository;
        $this->serviceRepository = $serviceRepository;
        $this->replicationControllerRepository = $replicationControllerRepository;
        $this->secretRepository = $secretRepository;
        $this->serviceAccountRepository = $serviceAccountRepository;
        $this->persistentVolumeClaimRepository = $persistentVolumeClaimRepository;
        $this->ingressRepository = $ingressRepository;
        $this->deploymentRepository = $deploymentRepository;
        $this->eventRepository = $eventRepository;
        $this->roleBindingRepository = $roleBindingRepository;
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

    /**
     * {@inheritdoc}
     */
    public function getSecretRepository()
    {
        return $this->secretRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceAccountRepository()
    {
        return $this->serviceAccountRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentVolumeClaimRepository()
    {
        return $this->persistentVolumeClaimRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getIngressRepository()
    {
        return $this->ingressRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeploymentRepository()
    {
        return $this->deploymentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventRepository()
    {
        return $this->eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleBindingRepository()
    {
        return $this->roleBindingRepository;
    }

    /**
     * @return KubernetesNamespace
     */
    public function getNamespace() : KubernetesNamespace
    {
        return $this->namespace;
    }

    public function withNamespace(KubernetesNamespace $namespace) : self
    {
        $client = clone $this;
        $client->namespace = $namespace;

        return $client;
    }
}
