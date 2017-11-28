<?php

namespace ContinuousPipe\AuditLog;

use DateTimeImmutable;

/**
 * Value object representing an audit log entry for an event.
 */
class Record
{
    /**
     * @var string
     */
    private $eventName;

    /**
     * @var DateTimeImmutable
     */
    private $eventDate;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @param string $eventName
     * @param string $eventType
     * @param array $data
     * @param DateTimeImmutable|null $eventDate
     */
    public function __construct(string $eventName, string $eventType, array $data, DateTimeImmutable $eventDate = null)
    {
        $this->eventName = $eventName;
        $this->eventDate = $eventDate ?: new DateTimeImmutable();
        $this->type = $eventType;
        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->eventName;
    }

    public function date(): DateTimeImmutable
    {
        return $this->eventDate;
    }
}
