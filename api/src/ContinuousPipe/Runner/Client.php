<?php

namespace ContinuousPipe\Runner;

use ContinuousPipe\Runner\Client\RunRequest;
use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;

interface Client
{
    /**
     * Creates a new run.
     *
     * @param RunRequest $request
     * @param User       $user
     *
     * @throws RunnerException
     *
     * @return Uuid
     */
    public function run(RunRequest $request, User $user);
}
