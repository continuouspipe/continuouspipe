<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;

interface FilterEvaluator
{
    /**
     * @param Tide  $tide
     * @param array $filter
     *
     * @throws TideConfigurationException
     *
     * @return bool
     */
    public function evaluates(Tide $tide, array $filter);
}
