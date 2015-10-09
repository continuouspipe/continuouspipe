<?php

namespace ContinuousPipe\River\Tide;

use ContinuousPipe\River\View\Tide;

class TideSummaryCreator
{
    public function fromTide(Tide $tide)
    {
        return new TideSummary(
            $tide->getStatus()
        );
    }
}
