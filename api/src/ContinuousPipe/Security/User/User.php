<?php

namespace ContinuousPipe\Security\User;

use ContinuousPipe\Security\Credentials\BucketContainer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class User implements BucketContainer
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var UuidInterface
     */
    private $bucketUuid;

    /**
     * @var string[]
     */
    private $roles;

    /**
     * @param string        $username
     * @param UuidInterface $bucketUuid
     * @param string[]      $roles
     */
    public function __construct($username, UuidInterface $bucketUuid, array $roles = [])
    {
        $this->username = $username;
        $this->bucketUuid = $bucketUuid;
        $this->roles = $roles;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param Uuid $uuid
     */
    public function setBucketUuid(Uuid $uuid)
    {
        $this->bucketUuid = $uuid;
    }

    /**
     * @return UuidInterface
     */
    public function getBucketUuid()
    {
        if (is_string($this->bucketUuid)) {
            $this->bucketUuid = Uuid::fromString($this->bucketUuid);
        }

        return $this->bucketUuid;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles ?: [];
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }
}
