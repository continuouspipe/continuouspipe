<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector;

use Kubernetes\Client\Model\DeploymentList;
use Kubernetes\Client\Model\IngressList;
use Kubernetes\Client\Model\PodList;
use Kubernetes\Client\Model\ReplicationControllerList;
use Kubernetes\Client\Model\ServiceList;

final class NamespaceSnapshot
{
    private $deployments;
    private $services;
    private $ingresses;
    private $pods;
    private $replicationControllers;

    public function __construct(
        DeploymentList $deployments,
        ReplicationControllerList $replicationControllers,
        ServiceList $services,
        IngressList $ingresses,
        PodList $pods
    ) {
        $this->deployments = $deployments;
        $this->replicationControllers = $replicationControllers;
        $this->services = $services;
        $this->ingresses = $ingresses;
        $this->pods = $pods;
    }

    public function getDeployments(): DeploymentList
    {
        return $this->deployments;
    }

    public function getReplicationControllers(): ReplicationControllerList
    {
        return $this->replicationControllers;
    }

    public function getServices(): ServiceList
    {
        return $this->services;
    }

    public function getIngresses(): IngressList
    {
        return $this->ingresses;
    }

    public function getPods(): PodList
    {
        return $this->pods;
    }
}
