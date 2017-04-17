<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector;

use ContinuousPipe\Adapter\Kubernetes\Inspector\ReverseTransformer\ComponentTransformer;
use ContinuousPipe\Model\Component;
use function GuzzleHttp\Promise\all;
use GuzzleHttp\Promise\PromiseInterface;
use Kubernetes\Client\NamespaceClient;

class NamespaceInspector
{
    /**
     * @var ComponentTransformer
     */
    private $reverseComponentTransformer;

    /**
     * @param ComponentTransformer $reverseComponentTransformer
     */
    public function __construct(ComponentTransformer $reverseComponentTransformer)
    {
        $this->reverseComponentTransformer = $reverseComponentTransformer;
    }

    /**
     * @param NamespaceClient $namespaceClient
     *
     * @return PromiseInterface
     */
    public function getComponents(NamespaceClient $namespaceClient)
    {
        return $this->snapshot($namespaceClient)->then(function (NamespaceSnapshot $snapshot) {
            return $this->reverseComponentTransformer->componentsFromSnapshot($snapshot);
        });
    }

    private function snapshot(NamespaceClient $namespaceClient) : PromiseInterface
    {
        return all([
            'deployments' => $namespaceClient->getDeploymentRepository()->asyncFindAll(),
            'replication_controllers' => $namespaceClient->getReplicationControllerRepository()->asyncFindAll(),
            'services' => $namespaceClient->getServiceRepository()->asyncFindAll(),
            'ingresses' => $namespaceClient->getIngressRepository()->asyncFindAll(),
            'pods' => $namespaceClient->getPodRepository()->asyncFindAll(),
        ])->then(function (array $objects) {
            return new NamespaceSnapshot(
                $objects['deployments'],
                $objects['replication_controllers'],
                $objects['services'],
                $objects['ingresses'],
                $objects['pods']
            );
        });
    }
}
