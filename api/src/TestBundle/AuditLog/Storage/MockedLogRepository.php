<?php

namespace TestBundle\AuditLog\Storage;

use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;

/**
 * This repository implementation intended to be used for functional tests.
 */
class MockedLogRepository implements LogRepository
{

    /**
     * {@inheritdoc}
     */
    public function insert(Record $record)
    {
        // Do nothing in tests.
    }
}
