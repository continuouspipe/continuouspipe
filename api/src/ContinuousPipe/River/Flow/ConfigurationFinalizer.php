<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface ConfigurationFinalizer
{
    /**
     * Finalize the configuration.
     *
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     * @param array         $configuration
     *
     * @return array
     */
    public function finalize(FlatFlow $flow, CodeReference $codeReference, array $configuration);
}
