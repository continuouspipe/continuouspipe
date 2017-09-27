<?php

namespace TestBundle\AuditLog\Storage;

use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;

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
}
