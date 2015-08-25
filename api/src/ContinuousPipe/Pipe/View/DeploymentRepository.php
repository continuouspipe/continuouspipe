<?php

namespace ContinuousPipe\Pipe\View;

use ContinuousPipe\Pipe\DeploymentNotFound;
use Rhumsaa\Uuid\Uuid;

interface DeploymentRepository
{
    /**
     * @param Uuid $uuid
     *
     * @throws DeploymentNotFound
     *
     * @return Deployment
     */
    public function find(Uuid $uuid);

    /**
     * Save the given deployment object.
     *
     * @param Deployment $deployment
     *
     * @return Deployment
     */
    public function save(Deployment $deployment);
}
