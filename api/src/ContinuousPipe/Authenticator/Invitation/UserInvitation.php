<?php

namespace ContinuousPipe\Authenticator\Invitation;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class UserInvitation
{
    /**
     * @JMS\Type("string")
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $userEmail;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $teamSlug;

    /**
     * @JMS\Type("array<string>")
     *
     * @var string[]
     */
    private $permissions;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @param UuidInterface $uuid
     * @param string $userEmail
     * @param string $teamSlug
     * @param array $permissions
     * @param \DateTimeInterface $creationDate
     */
    public function __construct(UuidInterface $uuid, $userEmail, $teamSlug, array $permissions, \DateTimeInterface $creationDate)
    {
        $this->uuid = $uuid;
        $this->userEmail = $userEmail;
        $this->teamSlug = $teamSlug;
        $this->permissions = $permissions;
        $this->creationDate = $creationDate;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * @return string
     */
    public function getTeamSlug()
    {
        return $this->teamSlug;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @return string[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
