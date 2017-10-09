<?php

namespace ContinuousPipe\Pipe\View;

use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Deployment
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    /**
     * @var string
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
     * @var User|null
     */
    private $user;

    /**
     * @var PublicEndpoint[]
     */
    private $publicEndpoints;

    /**
     * @var ComponentStatus[]
     */
    private $componentStatuses;

    /**
     * @param UuidInterface     $uuid
     * @param DeploymentRequest $request
     * @param string            $status
     * @param PublicEndpoint[]  $publicEndpoints
     * @param ComponentStatus[] $componentStatuses
     */
    public function __construct(UuidInterface $uuid, DeploymentRequest $request, $status, array $publicEndpoints = [], array $componentStatuses = [])
    {
        $this->uuid = (string) $uuid;
        $this->request = $request;
        $this->status = $status;
        $this->publicEndpoints = $publicEndpoints;
        $this->componentStatuses = $componentStatuses;
    }

    /**
     * @param DeploymentRequest $request
     * @param User              $user
     *
     * @return Deployment
     */
    public static function fromRequest(DeploymentRequest $request, User $user = null)
    {
        $deployment = new self(
            Uuid::uuid4(),
            $request,
            self::STATUS_PENDING
        );

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
     * @return User|null
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
     * @return PublicEndpoint[]
     */
    public function getPublicEndpoints()
    {
        return $this->publicEndpoints ?: [];
    }

    /**
     * @param PublicEndpoint[] $publicEndpoints
     */
    public function setPublicEndpoints($publicEndpoints)
    {
        $this->publicEndpoints = $publicEndpoints;
    }

    /**
     * @return ComponentStatus[]
     */
    public function getComponentStatuses()
    {
        return $this->componentStatuses ?: [];
    }

    /**
     * @param ComponentStatus[] $componentStatuses
     */
    public function setComponentStatuses($componentStatuses)
    {
        $this->componentStatuses = $componentStatuses;
    }
}
