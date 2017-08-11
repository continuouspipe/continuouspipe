<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\TideConfigurationException;

interface ConfigurationEnhancer
{
    /**
     * Enhance the loaded configurations for a given tide.
     *
     * This method should return the `configs` array with new (or not) elements.
     *
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     * @param array         $configs
     *
     * @throws TideConfigurationException
     *
     * @return array
     */
    public function enhance(FlatFlow $flow, CodeReference $codeReference, array $configs);
}
