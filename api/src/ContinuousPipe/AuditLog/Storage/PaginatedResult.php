<?php

namespace ContinuousPipe\AuditLog\Storage;

use ContinuousPipe\AuditLog\Record;

/**
 *
 */
class PaginatedResult
{
    /**
     * @var Record[]
     */
    private $records;

    /**
     * @var string
     */
    private $nextPageCursor;

    public function __construct(array $records, string $nextPageCursor)
    {
        $this->records = $records;
        $this->nextPageCursor = $nextPageCursor;
    }

    /**
     * @return Record[]
     */
    public function records(): array
    {
        return $this->records;
    }

    /**
     * @return string
     */
    public function nextPageCursor(): string
    {
        return $this->nextPageCursor;
    }
}
