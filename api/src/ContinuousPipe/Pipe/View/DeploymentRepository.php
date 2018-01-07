<?php

namespace ContinuousPipe\Pipe\View;

use ContinuousPipe\Pipe\DeploymentNotFound;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface DeploymentRepository
{
    /**
     * @param UuidInterface $uuid
     *
     * @throws DeploymentNotFound
     *
     * @return Deployment
     */
    public function find(UuidInterface $uuid);

    /**
     * Save the given deployment object.
     *
     * @param Deployment $deployment
     *
     * @return Deployment
     */
    public function save(Deployment $deployment);
}
