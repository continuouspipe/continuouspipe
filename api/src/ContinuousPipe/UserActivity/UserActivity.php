<?php

namespace ContinuousPipe\UserActivity;

use ContinuousPipe\River\CodeRepository\CodeRepositoryUser;
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
     * @JMS\Type("ContinuousPipe\River\CodeRepository\CodeRepositoryUser")
     *
     * @var CodeRepositoryUser
     */
    private $user;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $dateTime;

    public function __construct(UuidInterface $flowUuid, string $type, CodeRepositoryUser $user, \DateTimeInterface $dateTime)
    {
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
     * @return CodeRepositoryUser
     */
    public function getUser() : CodeRepositoryUser
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

    /**
     * @return \DateTimeInterface
     */
    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }
}
