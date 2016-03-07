<?php

namespace ContinuousPipe\River\Tide\Concurrency;

use ContinuousPipe\River\View\Tide;

interface TideConcurrencyManager
{
    /**
     * Delegate the responsibility to the concurrency manager to decide if we should start
     * or not the tide.
     *
     * @param Tide $tide
     *
     * @return bool
     */
    public function shouldTideStart(Tide $tide);
}
