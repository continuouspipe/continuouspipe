<?php

namespace ContinuousPipe\River\Alerts;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface AlertsRepository
{
    /**
     * @param FlatFlow $flow
     *
     * @return Alert[]
     */
    public function findByFlow(FlatFlow $flow);
}
