<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests;

use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\DeploymentRepository;
use Kubernetes\Client\Repository\IngressRepository;
use Kubernetes\Client\Repository\PersistentVolumeClaimRepository;
use Kubernetes\Client\Repository\PodRepository;
use Kubernetes\Client\Repository\ReplicationControllerRepository;
use Kubernetes\Client\Repository\SecretRepository;
use Kubernetes\Client\Repository\ServiceAccountRepository;
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
     * @param PodRepository                   $podRepository
     * @param ServiceRepository               $serviceRepository
     * @param ReplicationControllerRepository $replicationControllerRepository
     * @param SecretRepository                $secretRepository
     * @param ServiceAccountRepository        $serviceAccountRepository
     * @param PersistentVolumeClaimRepository $persistentVolumeClaimRepository
     * @param IngressRepository               $ingressRepository
     * @param DeploymentRepository            $deploymentRepository
     */
    public function __construct(PodRepository $podRepository, ServiceRepository $serviceRepository, ReplicationControllerRepository $replicationControllerRepository, SecretRepository $secretRepository, ServiceAccountRepository $serviceAccountRepository, PersistentVolumeClaimRepository $persistentVolumeClaimRepository, IngressRepository $ingressRepository, DeploymentRepository $deploymentRepository)
    {
        $this->podRepository = $podRepository;
        $this->serviceRepository = $serviceRepository;
        $this->replicationControllerRepository = $replicationControllerRepository;
        $this->secretRepository = $secretRepository;
        $this->serviceAccountRepository = $serviceAccountRepository;
        $this->persistentVolumeClaimRepository = $persistentVolumeClaimRepository;
        $this->ingressRepository = $ingressRepository;
        $this->deploymentRepository = $deploymentRepository;
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
}
