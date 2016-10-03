<?php

namespace ContinuousPipe\River\Alerts;

use ContinuousPipe\River\Flow;

interface AlertsRepository
{
    /**
     * @param Flow $flow
     *
     * @return Alert[]
     */
    public function findByFlow(Flow $flow);
}
