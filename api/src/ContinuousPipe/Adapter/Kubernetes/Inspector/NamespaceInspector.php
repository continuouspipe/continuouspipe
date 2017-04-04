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
        return all([
            $namespaceClient->getReplicationControllerRepository()->asyncFindAll()->then(function ($replicationControllers) use ($namespaceClient) {
                $components = [];
                foreach ($replicationControllers as $replicationController) {
                    try {
                        $components[] = $this->reverseComponentTransformer->getComponentFromReplicationController($namespaceClient, $replicationController);
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }
                }
                return $components;
            }),
            $namespaceClient->getDeploymentRepository()->asyncFindAll()->then(function ($deployments) use ($namespaceClient) {
                $components = [];
                foreach ($deployments as $deployment) {
                    try {
                        $components[] = $this->reverseComponentTransformer->getComponentFromDeployment($namespaceClient, $deployment);
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }
                }
                return $components;
            })
        ])->then(function (array $repoComponents) {
            return call_user_func_array('array_merge', $repoComponents);
        });
    }
}
