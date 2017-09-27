<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use TestBundle\AuditLog\Storage\TracedLogRepository;

class AuditLogContext implements Context
{

    /**
     * @var LogRepository
     */
    private $logRepository;

    public function __construct(TracedLogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @Given a record should be added to the audit log storage
     */
    public function aRecordShouldBeAddedToTheAuditLogStorage()
    {
        $insertedRecords = $this->logRepository->getInsertedRecords();
        if (0 == count($insertedRecords)) {
            throw new UnexpectedValueException('Expected to find at least one inserted record.');
        }

        foreach ($insertedRecords as $record) {
            /** @var \ContinuousPipe\AuditLog\Record $record */
            var_export($record->data());
        }
    }

    /**
     * @Given no record should be added to the audit log storage
     */
    public function noRecordShouldBeAddedToTheAuditLogStorage()
    {
        $insertedRecords = $this->logRepository->getInsertedRecords();
        if (count($insertedRecords) !== 0) {
            throw new UnexpectedValueException('Expected to do not find any inserted record.');
        }
    }
}
