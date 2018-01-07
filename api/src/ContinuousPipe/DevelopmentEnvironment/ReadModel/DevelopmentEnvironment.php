<?php

namespace ContinuousPipe\DevelopmentEnvironment\ReadModel;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class DevelopmentEnvironment
{
    /**
     * @JMS\Type("uuid")
     * @JMS\Groups({"Default"})
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @JMS\Type("uuid")
     * @JMS\Groups({"Default"})
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"Default"})
     *
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
