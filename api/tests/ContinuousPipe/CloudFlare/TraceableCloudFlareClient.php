<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

class TraceableCloudFlareClient implements CloudFlareClient
{
    /**
     * @var ZoneRecord[]
     */
    private $createdRecords = [];

    /**
     * @var string[]
     */
    private $deletedRecords = [];

    /**
     * @var CloudFlareClient
     */
    private $decoratedClient;

    /**
     * @param CloudFlareClient $decoratedClient
     */
    public function __construct(CloudFlareClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record) : string
    {
        $identifier = $this->decoratedClient->createRecord($zone, $authentication, $record);

        $this->createdRecords[] = $record;

        return $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(string $zone, CloudFlareAuthentication $authentication, string $recordIdentifier)
    {
        $this->decoratedClient->deleteRecord($zone, $authentication, $recordIdentifier);

        $this->deletedRecords[] = $recordIdentifier;
    }

    /**
     * @return ZoneRecord[]
     */
    public function getCreatedRecords(): array
    {
        return $this->createdRecords;
    }

    /**
     * @return string[]
     */
    public function getDeletedRecords(): array
    {
        return $this->deletedRecords;
    }
}
