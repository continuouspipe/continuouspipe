<?php

namespace AuthenticatorTestBundle\AuditLog\Storage;

use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use ContinuousPipe\AuditLog\Storage\PaginatedResult;

class TracedLogRepository implements LogRepository
{

    /**
     * @var LogRepository
     */
    private $decoratedLogRepository;

    /**
     * @var array
     */
    private $insertedRecords = [];

    public function __construct(LogRepository $decoratedLogRepository)
    {
        $this->decoratedLogRepository = $decoratedLogRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Record $record)
    {
        $this->decoratedLogRepository->insert($record);

        $this->insertedRecords[] = $record;
    }

    public function getInsertedRecords(): array
    {
        return $this->insertedRecords;
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $eventType, string $pageCursor, int $pageSize): PaginatedResult
    {
        return $this->decoratedLogRepository->query($eventType, $pageCursor, $pageSize);
    }

    /**
     * {@inheritdoc}
     */
    public function listEventTypes(): array
    {
        return $this->decoratedLogRepository->listEventTypes();
    }
}
