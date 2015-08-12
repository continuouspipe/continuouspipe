<?php

namespace ContinuousPipe\Adapter\Kubernetes\Naming;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Environment;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\Label;
use Kubernetes\Client\Model\ObjectMetadata;

class IdentifierNamingStrategy implements NamingStrategy
{
    /**
     * {@inheritdoc}
     */
    public function getObjectMetadataFromComponent(Component $component)
    {
        return new ObjectMetadata($component->getIdentifier(), $this->getLabelsByComponent($component));
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelsByComponent(Component $component)
    {
        return new KeyValueObjectList([
            new Label('component-identifier', $component->getIdentifier()),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentNamespace(Environment $environment)
    {
        $namespaceName = $environment->getIdentifier();

        return new KubernetesNamespace(new ObjectMetadata($namespaceName));
    }
}
