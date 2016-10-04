<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideEvent;

interface TideSaga
{
    public function notify(TideEvent $event);
}
