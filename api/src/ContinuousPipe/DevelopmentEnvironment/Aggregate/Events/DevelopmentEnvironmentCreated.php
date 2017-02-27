<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate\Events;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class DevelopmentEnvironmentCreated extends DevelopmentEnvironmentEvent
{
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
    private $username;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $dateTime;

    public function __construct(UuidInterface $developmentEnvironmentUuid, UuidInterface $flowUuid, string $username, string $name, \DateTimeInterface $dateTime)
    {
        parent::__construct($developmentEnvironmentUuid);

        $this->flowUuid = $flowUuid;
        $this->username = $username;
        $this->name = $name;
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
    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }
}
