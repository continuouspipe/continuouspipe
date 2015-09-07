<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector;

use ContinuousPipe\Adapter\Kubernetes\ReverseTransformer\ComponentTransformer;
use ContinuousPipe\Model\Component;
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
     * @return Component[]
     */
    public function getComponents(NamespaceClient $namespaceClient)
    {
        $replicationControllers = $namespaceClient->getReplicationControllerRepository()->findAll();
        $components = [];

        foreach ($replicationControllers as $replicationController) {
            try {
                $components[] = $this->reverseComponentTransformer->getComponentFromReplicationController($namespaceClient, $replicationController);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        return $components;
    }
}
