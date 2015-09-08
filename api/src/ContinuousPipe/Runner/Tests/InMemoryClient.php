<?php

namespace ContinuousPipe\Runner\Tests;

use ContinuousPipe\Runner\Client;
use ContinuousPipe\Runner\Client\RunRequest;
use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;

class InMemoryClient implements Client
{
    /**
     * {@inheritdoc}
     */
    public function run(RunRequest $request, User $user)
    {
        return Uuid::uuid1();
    }
}
