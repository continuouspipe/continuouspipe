<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;

interface BuilderClient
{
    /**
     * Start an image build.
     *
     * @param BuildRequest $buildRequest
     *
     * @throws BuilderException
     *
     * @return BuilderBuild
     */
    public function build(BuildRequest $buildRequest, User $user);
}
