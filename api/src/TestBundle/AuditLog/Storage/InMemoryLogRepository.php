<?php

namespace TestBundle\AuditLog\Storage;

use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use ContinuousPipe\AuditLog\Storage\PaginatedResult;

/**
 * This repository implementation intended to be used for functional tests.
 */
class InMemoryLogRepository implements LogRepository
{
    /**
     * @var array
     */
    private $recordsByType;

    /**
     * @var array
     */
    private $eventTypes = [];

    /**
     * {@inheritdoc}
     */
    public function insert(Record $record)
    {
        $this->recordsByType[$record->type()][] = $record;
        $this->eventTypes = array_unique(array_merge($this->eventTypes, [$record->type()]));
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $eventType, string $pageCursor, int $pageSize): PaginatedResult
    {
        $result = isset($this->recordsByType[$eventType]) ? $this->recordsByType[$eventType] : [];
        $records = array_slice($result, (int) $pageCursor, $pageSize);
        return new PaginatedResult($records, (int) $pageCursor + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function listEventTypes(): array
    {
        return $this->eventTypes;
    }
}
