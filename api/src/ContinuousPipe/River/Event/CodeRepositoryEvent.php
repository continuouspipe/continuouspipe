<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

interface CodeRepositoryEvent
{
    /**
     * @return UuidInterface
     */
    public function getFlowUuid();

    /**
     * @return CodeReference
     */
    public function getCodeReference();
}
