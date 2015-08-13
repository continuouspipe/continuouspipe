<?php

namespace ContinuousPipe\Pipe;

use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class Deployment
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $status;

    /**
     * @var DeploymentRequest
     */
    private $request;

    /**
     * @param DeploymentRequest $request
     *
     * @return Deployment
     */
    public static function fromRequest(DeploymentRequest $request)
    {
        $deployment = new self();
        $deployment->uuid = Uuid::uuid1();
        $deployment->request = $request;
        $deployment->status = self::STATUS_PENDING;

        return $deployment;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return DeploymentRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
}
