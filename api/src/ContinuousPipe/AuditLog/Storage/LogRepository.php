<?php

namespace ContinuousPipe\AuditLog\Storage;

use ContinuousPipe\AuditLog\Exception\OperationFailedException;
use ContinuousPipe\AuditLog\Record;

interface LogRepository
{
    /**
     * Save the record to a permanent storage.
     *
     * @param Record $record
     *
     * @throws OperationFailedException
     */
    public function insert(Record $record);

    /**
     * Fetch log records from storage.
     *
     * @param string $eventType Filter record for this type.
     * @param string $pageCursor
     * @param int $pageSize
     * @return PaginatedResult
     *
     * @throws OperationFailedException
     */
    public function query(string $eventType, string $pageCursor, int $pageSize): PaginatedResult;

    /**
     * Return the list of available log record types.
     *
     * @return string[]
     */
    public function listEventTypes(): array;
}
