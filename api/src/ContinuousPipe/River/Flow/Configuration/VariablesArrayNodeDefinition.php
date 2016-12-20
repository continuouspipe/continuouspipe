<?php

namespace ContinuousPipe\River\Flow\Configuration;

class VariablesArrayNodeDefinition extends ParameterizedArrayNodeDefinition
{
    protected function getNodeClass() : string
    {
        return VariablesArrayNode::class;
    }
}
