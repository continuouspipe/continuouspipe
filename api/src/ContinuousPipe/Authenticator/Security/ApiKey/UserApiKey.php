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

    /**
     * @var string
     */
    private $description;

    public function __construct(UuidInterface $uuid, User $user, string $apiKey, \DateTimeInterface $creationDate, string $description = null)
    {
        $this->uuid = $uuid;
        $this->creationDate = $creationDate;
        $this->apiKey = $apiKey;
        $this->user = $user;
        $this->description = $description;
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

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
