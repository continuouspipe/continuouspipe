<?php

namespace ContinuousPipe\Pipe;

use Ramsey\Uuid\Uuid;

interface DeploymentRepository
{
    /**
     * Find a deployment by its uuid.
     *
     * @param Uuid $uuid
     *
     * @throws DeploymentNotFound
     *
     * @return Deployment
     */
    public function find(Uuid $uuid);
}
