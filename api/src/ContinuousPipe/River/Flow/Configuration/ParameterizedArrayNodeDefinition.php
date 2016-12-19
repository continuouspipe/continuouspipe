<?php

namespace ContinuousPipe\River\Flow\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

abstract class ParameterizedArrayNodeDefinition extends ArrayNodeDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function createNode()
    {
        $class = $this->getNodeClass();
        $node = new $class($this->name, $this->parent);
        $this->validatePrototypeNode($node);

        if (null !== $this->key) {
            $node->setKeyAttribute($this->key, $this->removeKeyItem);
        }

        if (true === $this->atLeastOne) {
            $node->setMinNumberOfElements(1);
        }

        if ($this->default) {
            $node->setDefaultValue($this->defaultValue);
        }

        if (false !== $this->addDefaultChildren) {
            $node->setAddChildrenIfNoneSet($this->addDefaultChildren);
            if ($this->prototype instanceof static && null === $this->prototype->prototype) {
                $this->prototype->addDefaultsIfNotSet();
            }
        }

        $this->prototype->parent = $node;
        $node->setPrototype($this->prototype->getNode());
        $node->setAllowNewKeys($this->allowNewKeys);
        $node->addEquivalentValue(null, $this->nullEquivalent);
        $node->addEquivalentValue(true, $this->trueEquivalent);
        $node->addEquivalentValue(false, $this->falseEquivalent);
        $node->setPerformDeepMerging($this->performDeepMerging);
        $node->setRequired($this->required);
        $node->setIgnoreExtraKeys($this->ignoreExtraKeys, $this->removeExtraKeys);
        $node->setNormalizeKeys($this->normalizeKeys);

        if (null !== $this->normalization) {
            $node->setNormalizationClosures($this->normalization->before);
            $node->setXmlRemappings($this->normalization->remappings);
        }

        if (null !== $this->merge) {
            $node->setAllowOverwrite($this->merge->allowOverwrite);
            $node->setAllowFalse($this->merge->allowFalse);
        }

        if (null !== $this->validation) {
            $node->setFinalValidationClosures($this->validation->rules);
        }

        return $node;
    }

    /**
     * Get node class.
     *
     * @return string
     */
    abstract protected function getNodeClass() : string;
}
