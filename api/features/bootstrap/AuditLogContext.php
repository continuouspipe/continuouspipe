<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\AuditLog\Record;
use ContinuousPipe\AuditLog\Storage\LogRepository;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use AuthenticatorTestBundle\AuditLog\Storage\TracedLogRepository;

class AuditLogContext implements Context
{
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(TracedLogRepository $logRepository, Client $httpClient)
    {
        $this->logRepository = $logRepository;
        $this->httpClient = $httpClient;
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
        $this->httpClient->request(Request::METHOD_GET, '/audit-log/view', ['event_type' => $eventType]);
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

    private function assertResponseCode(int $expectedHttpStatus)
    {
        if (($actualHttpStatus = $this->httpClient->getInternalResponse()->getStatus()) !== $expectedHttpStatus) {
            echo $this->httpClient->getCrawler()->text();
            throw new UnexpectedValueException(sprintf(
                'Expected to get HTTP status code %d, but got %d.',
                $expectedHttpStatus,
                $actualHttpStatus
            ));
        }
    }

    private function assertContainsText(string $text)
    {
        if (false === mb_stripos($this->httpClient->getCrawler()->text(), $text)) {
            throw new UnexpectedValueException(sprintf(
                'Page expected to contain the text "%s", but does not found.',
                $text
            ));
        }
    }

    private function assertDoesNotContainText($text)
    {
        if (false !== mb_stripos($this->httpClient->getCrawler()->text(), $text)) {
            throw new UnexpectedValueException(sprintf(
                'Page not expected to contain the text "%s", but found.',
                $text
            ));
        }
    }
}
