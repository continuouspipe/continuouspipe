<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface TideConfigurationFactory
{
    const FILENAME = 'continuous-pipe.yml';

    /**
     * Get the configuration of the given tide.
     *
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     *
     * @return array
     *
     * @throws TideConfigurationException
     */
    public function getConfiguration(FlatFlow $flow, CodeReference $codeReference);
}
