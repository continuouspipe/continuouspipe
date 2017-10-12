<?php

namespace ContinuousPipe\Pipe\Kubernetes\Naming;

use Cocur\Slugify\Slugify;
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
        $labels = [
            new Label('component-identifier', $component->getIdentifier()),
        ];

        foreach ($component->getLabels() as $key => $value) {
            $labels[] = new Label($key, $this->prepareLabelValue($value));
        }

        return new KeyValueObjectList($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentNamespace(Environment $environment)
    {
        $namespaceName = $environment->getIdentifier();

        $labels = new KeyValueObjectList([
            new Label('continuous-pipe-environment', $environment->getIdentifier()),
            new Label('created-by', 'continuous-pipe'),
        ]);

        foreach ($environment->getLabels() as $key => $value) {
            $labels->add(new Label($key, $value));
        }

        return new KubernetesNamespace(new ObjectMetadata($namespaceName, $labels));
    }

    /**
     * Transform label values when needed.
     *
     * @param string $value
     *
     * @return string
     */
    private function prepareLabelValue($value)
    {
        if (!preg_match('#^(([A-Za-z0-9][-A-Za-z0-9_.]*)?[A-Za-z0-9])?$#', $value)) {
            $value = (new Slugify())->slugify($value);
        }

        return $value;
    }
}
