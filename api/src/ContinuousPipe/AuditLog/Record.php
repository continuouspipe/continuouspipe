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
     * @param string $type
     * @param array $data
     */
    public function __construct(string $eventName, string $type, array $data)
    {
        $this->eventName = $eventName;
        $this->eventDate = new DateTimeImmutable();
        $this->type = $type;
        $this->data = $data;
    }

    public function data(): array
    {
        return array_merge($this->data, [
            'event_name' => $this->eventName,
            'event_date' => $this->eventDate->format(\DateTime::W3C),
        ]);
    }

    public function type(): string
    {
        return $this->type;
    }
}
