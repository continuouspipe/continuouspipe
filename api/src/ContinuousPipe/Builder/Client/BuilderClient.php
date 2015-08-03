<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\User\User;

interface BuilderClient
{
    /**
     * Start an image build.
     *
     * @param BuildRequest $buildRequest
     *
     * @return BuilderBuild
     */
    public function build(BuildRequest $buildRequest, User $user);
}
