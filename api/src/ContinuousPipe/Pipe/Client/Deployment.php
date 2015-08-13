<?php

namespace ContinuousPipe\Pipe\Client;

use ContinuousPipe\User\User;
use JMS\Serializer\Annotation as JMS;
use Rhumsaa\Uuid\Uuid;

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
     * @JMS\Type("ContinuousPipe\Pipe\Client\DeploymentRequest")
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
     * @JMS\Type("array<ContinuousPipe\Pipe\Client\PublicEndpoint>")
     *
     * @var PublicEndpoint[]
     */
    private $publicEndpoints;

    /**
     * @param Uuid              $uuid
     * @param DeploymentRequest $request
     * @param string            $status
     */
    public function __construct(Uuid $uuid, DeploymentRequest $request, $status)
    {
        $this->uuid = (string) $uuid;
        $this->request = $request;
        $this->status = $status;
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
     * @return bool
     */
    public function isSuccessful()
    {
        return self::STATUS_SUCCESS == $this->getStatus();
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return self::STATUS_FAILURE == $this->getStatus();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return PublicEndpoint[]
     */
    public function getPublicEndpoints()
    {
        return $this->publicEndpoints;
    }
}
