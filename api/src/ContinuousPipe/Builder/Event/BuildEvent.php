<?php

namespace ContinuousPipe\Builder\Event;

use ContinuousPipe\Builder\Build;

interface BuildEvent
{
    /**
     * @return Build
     */
    public function getBuild();
}
