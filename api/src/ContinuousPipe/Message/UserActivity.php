<?php

namespace ContinuousPipe\Message;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class UserActivity
{
    const TYPE_PUSH = 'push';

    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("ContinuousPipe\Message\UserActivityUser")
     *
     * @var UserActivityUser
     */
    private $user;

    public function __construct(UuidInterface $flowUuid, string $type, UserActivityUser $user)
    {
        $this->flowUuid = $flowUuid;
        $this->type = $type;
        $this->user = $user;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    /**
     * @return UserActivityUser
     */
    public function getUser() : UserActivityUser
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
}
