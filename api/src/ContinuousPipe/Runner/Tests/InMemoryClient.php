<?php

namespace ContinuousPipe\Runner\Tests;

use ContinuousPipe\Runner\Client;
use ContinuousPipe\Runner\Client\RunRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;

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
