<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector;

use ContinuousPipe\Adapter\Kubernetes\ReverseTransformer\ComponentTransformer;
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
        $self = $this;

        return all([
            $namespaceClient->getReplicationControllerRepository()->asyncFindAll()->then(function ($replicationControllers) use ($self, $namespaceClient) {
                $components = [];
                foreach ($replicationControllers as $replicationController) {
                    try {
                        $components[] = $self->reverseComponentTransformer->getComponentFromReplicationController($namespaceClient, $replicationController);
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }
                }
                return $components;
            }),
            $namespaceClient->getDeploymentRepository()->asyncFindAll()->then(function ($deployments) use ($self, $namespaceClient) {
                $components = [];
                foreach ($deployments as $deployment) {
                    try {
                        $components[] = $self->reverseComponentTransformer->getComponentFromDeployment($namespaceClient, $deployment);
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }
                }
                return $components;
            })
        ]);
    }
}
