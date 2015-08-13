<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class Deployment
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @JMS\Type("ContinuousPipe\Pipe\DeploymentRequest")
     *
     * @var DeploymentRequest
     */
    private $request;

    /**
     * @JMS\Type("ContinuousPipe\User\User")
     *
     * @var User
     */
    private $user;

    /**
     * @param DeploymentRequest $request
     * @param User $user
     *
     * @return Deployment
     */
    public static function fromRequest(DeploymentRequest $request, User $user)
    {
        $deployment = new self();
        $deployment->uuid = (string) Uuid::uuid1();
        $deployment->request = $request;
        $deployment->status = self::STATUS_PENDING;
        $deployment->user = $user;

        return $deployment;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return Uuid::fromString($this->uuid);
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

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $status
     */
    public function updateStatus($status)
    {
        $this->status = $status;
    }
}
