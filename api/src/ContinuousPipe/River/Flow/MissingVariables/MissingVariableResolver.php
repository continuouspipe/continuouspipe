<?php

namespace ContinuousPipe\River\Flow\MissingVariables;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface MissingVariableResolver
{
    /**
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     *
     * @return string[]
     */
    public function findMissingVariables(FlatFlow $flow, CodeReference $codeReference) : array;
}
