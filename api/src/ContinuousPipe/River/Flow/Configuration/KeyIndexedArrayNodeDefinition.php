<?php

namespace ContinuousPipe\River\Flow\Configuration;

class KeyIndexedArrayNodeDefinition extends ParameterizedArrayNodeDefinition
{
    protected function getNodeClass() : string
    {
        return KeyIndexedArrayNode::class;
    }
}
