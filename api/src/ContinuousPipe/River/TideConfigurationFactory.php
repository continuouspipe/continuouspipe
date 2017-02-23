<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface TideConfigurationFactory
{
    const FILENAME = 'continuous-pipe.yml';

    /**
     * Get the configuration of the given tide.
     *
     * If you don't want the configuration to be validated, pass false to the `$validated` argument.
     *
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     * @param bool          $validated
     *
     * @return array
     *
     * @throws TideConfigurationException
     */
    public function getConfiguration(FlatFlow $flow, CodeReference $codeReference, bool $validated = true);
}
