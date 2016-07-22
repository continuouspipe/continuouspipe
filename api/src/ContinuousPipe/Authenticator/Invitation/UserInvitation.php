<?php

namespace ContinuousPipe\Authenticator\Invitation;

class UserInvitation
{
    /**
     * @var string
     */
    private $userEmail;

    /**
     * @var string
     */
    private $teamSlug;

    /**
     * @var string[]
     */
    private $permissions;

    /**
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @param string             $userEmail
     * @param string             $teamSlug
     * @param array              $permissions
     * @param \DateTimeInterface $creationDate
     */
    public function __construct($userEmail, $teamSlug, array $permissions, \DateTimeInterface $creationDate)
    {
        $this->userEmail = $userEmail;
        $this->teamSlug = $teamSlug;
        $this->permissions = $permissions;
        $this->creationDate = $creationDate;
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
