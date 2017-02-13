<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserApiKey
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @var User
     */
    private $user;

    public function __construct(UuidInterface $uuid, User $user, string $apiKey, \DateTimeInterface $creationDate)
    {
        $this->uuid = $uuid;
        $this->creationDate = $creationDate;
        $this->apiKey = $apiKey;
        $this->user = $user;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }
}
