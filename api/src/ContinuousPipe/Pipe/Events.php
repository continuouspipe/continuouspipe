<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\Kubernetes\Event\Environment\EnvironmentDeletionEvent;

final class Events
{
    /**
     * This event will be dispatch before the deletion of an environment.
     *
     * The listeners will receive the event `EnvironmentDeletionEvent`.
     *
     * @see EnvironmentDeletionEvent
     */
    const ENVIRONMENT_PRE_DELETION = 'environment.pre_deletion';
}
