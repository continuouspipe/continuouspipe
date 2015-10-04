<?php

namespace ContinuousPipe\River;

interface TideConfigurationFactory
{
    const FILENAME = 'continuous-pipe.yml';

    /**
     * Get the configuration of the given tide.
     *
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return array
     *
     * @throws TideConfigurationException
     */
    public function getConfiguration(Flow $flow, CodeReference $codeReference);
}
