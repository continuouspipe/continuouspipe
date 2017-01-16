<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class Build implements \JsonSerializable
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("ContinuousPipe\Builder\Request\BuildRequest")
     *
     * @var BuildRequest
     */
    private $request;

    /**
     * @JMS\Type("ContinuousPipe\Security\User\User")
     *
     * @var User
     */
    private $user;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @param string $uuid
     * @param BuildRequest $request
     * @param User $user
     * @param string $status
     */
    public function __construct(string $uuid, BuildRequest $request, User $user, string $status)
    {
        $this->uuid = $uuid;
        $this->request = $request;
        $this->user = $user;
        $this->status = $status;
    }

    /**
     * @return BuildRequest
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
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'build-'.$this->uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => (string) $this->uuid,
            'status' => $this->status,
        ];
    }
}
