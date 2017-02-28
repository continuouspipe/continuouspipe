<?php

namespace ContinuousPipe\DevelopmentEnvironment\ReadModel;

use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class DevelopmentEnvironment
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTimeInterface
     */
    private $modifiedAt;

    public function __construct(UuidInterface $uuid, UuidInterface $flowUuid, string $username, string $name, \DateTimeInterface $modifiedAt)
    {
        $this->uuid = $uuid;
        $this->flowUuid = $flowUuid;
        $this->username = $username;
        $this->name = $name;
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getModifiedAt(): \DateTimeInterface
    {
        return $this->modifiedAt;
    }
}
