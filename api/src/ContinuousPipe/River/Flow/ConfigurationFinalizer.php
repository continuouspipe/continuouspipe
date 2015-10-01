<?php

namespace ContinuousPipe\River\Flow;

interface ConfigurationFinalizer
{
    /**
     * Finalize configuration by adjusting it, merging duplicates and/or adding BC layer.
     *
     * @param array $configuration
     *
     * @return array
     */
    public function finalize(array $configuration);
}
