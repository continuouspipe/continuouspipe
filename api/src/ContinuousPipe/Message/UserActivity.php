<?php

namespace ContinuousPipe\Message;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class UserActivity implements Message
{
    const TYPE_PUSH = 'push';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $teamSlug;

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

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $dateTime;

    public function __construct(string $teamSlug, UuidInterface $flowUuid, string $type, UserActivityUser $user, \DateTimeInterface $dateTime)
    {
        $this->teamSlug = $teamSlug;
        $this->flowUuid = $flowUuid;
        $this->type = $type;
        $this->user = $user;
        $this->dateTime = $dateTime;
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
     * @return \DateTimeInterface
     */
    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }

    /**
     * @return string
     */
    public function getTeamSlug(): string
    {
        return $this->teamSlug;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
}
