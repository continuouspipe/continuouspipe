<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use Helper\KernelClientHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use TestBundle\AuditLog\Storage\TracedLogRepository;

class AuditLogContext implements Context
{
    use KernelClientHelper;

    /**
     * @var LogRepository
     */
    private $logRepository;

    public function __construct(TracedLogRepository $logRepository, KernelInterface $kernel)
    {
        $this->logRepository = $logRepository;
        $this->kernel = $kernel;
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
            /** @var Record $record */
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

    /**
     * @Given these records exist in audit log storage
     */
    public function theseRecordsExistInAuditLogStorage(TableNode $table)
    {
        foreach ($table->getColumnsHash() as $row) {
            $data = [];
            foreach (explode(',', $row['Properties']) as $property) {
                list($key, $value) = explode('=', $property);
                $data[$key] = $value;
            }
            $eventDate = DateTimeImmutable::createFromFormat(DateTime::W3C, $row['Event Date']);
            $record = new Record($row['Event Name'], $row['Event Type'], $data, $eventDate);
            $this->logRepository->insert($record);
        }
    }

    /**
     * @When I visit the audit log view page of type :eventType
     */
    public function iVisitTheAuditLogViewPage($eventType)
    {
        $this->request(Request::create('/audit-log/view', Request::METHOD_GET, ['event_type' => $eventType]));
    }

    /**
     * @Then I should see these list of audit log records
     */
    public function iShouldSeeTheseListOfAuditLogRecords(TableNode $table)
    {
        $this->assertResponseCode(200);

        foreach ($table->getColumnsHash() as $row) {
            foreach ($row as $fieldName => $value) {
                $this->assertContainsText($value);
            }
        }
    }

    /**
     * @Then I should not see these list of audit log records
     */
    public function iShouldNotSeeTheseListOfAuditLogRecords(TableNode $table)
    {
        $this->assertResponseCode(200);

        foreach ($table->getColumnsHash() as $row) {
            foreach ($row as $fieldName => $value) {
                $this->assertDoesNotContainText($value);
            }
        }
    }
}
