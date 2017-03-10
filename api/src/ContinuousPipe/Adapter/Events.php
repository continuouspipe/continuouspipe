<?php

namespace ContinuousPipe\Adapter;

use ContinuousPipe\Pipe\Event\Environment\EnvironmentDeletion;

final class Events
{
    /**
     * This event will be dispatch before the deletion of an environment.
     *
     * The listeners will receive the event `EnvironmentDeletion`.
     *
     * @see EnvironmentDeletion
     *
     */
    const ENVIRONMENT_PRE_DELETION = 'environment.pre_deletion';
}
