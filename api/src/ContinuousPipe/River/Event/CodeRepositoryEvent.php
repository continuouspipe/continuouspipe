<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;

interface CodeRepositoryEvent
{
    /**
     * @return Flow
     */
    public function getFlow();

    /**
     * @return CodeReference
     */
    public function getCodeReference();
}
