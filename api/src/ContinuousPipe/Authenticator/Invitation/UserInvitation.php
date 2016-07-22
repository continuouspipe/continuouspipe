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
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @param string             $userEmail
     * @param string             $teamSlug
     * @param \DateTimeInterface $creationDate
     */
    public function __construct($userEmail, $teamSlug, \DateTimeInterface $creationDate)
    {
        $this->userEmail = $userEmail;
        $this->teamSlug = $teamSlug;
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
}
