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
}
