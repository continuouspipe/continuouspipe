<?php

namespace ContinuousPipe\River\Alerts;

use ContinuousPipe\River\View\Flow;

interface AlertsRepository
{
    /**
     * @param Flow $flow
     *
     * @return Alert[]
     */
    public function findByFlow(Flow $flow);
}
