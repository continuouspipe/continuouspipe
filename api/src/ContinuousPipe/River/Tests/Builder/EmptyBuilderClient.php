<?php

namespace ContinuousPipe\River\Tests\Builder;

use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;

class EmptyBuilderClient implements BuilderClient
{
    /**
     * {@inheritdoc}
     */
    public function build(BuildRequest $buildRequest, User $user)
    {
        return new BuilderBuild((string) Uuid::uuid1(), BuilderBuild::STATUS_PENDING);
    }
}
