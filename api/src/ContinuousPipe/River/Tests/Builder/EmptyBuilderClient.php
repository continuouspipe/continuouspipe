<?php

namespace ContinuousPipe\River\Tests\Builder;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;

class EmptyBuilderClient implements BuilderClient
{
    /**
     * {@inheritdoc}
     */
    public function build(BuildRequest $buildRequest, User $user) : Build
    {
        return new Build(
            (string) Uuid::uuid1(),
            $buildRequest,
            $user,
            Build::STATUS_PENDING
        );
    }
}
