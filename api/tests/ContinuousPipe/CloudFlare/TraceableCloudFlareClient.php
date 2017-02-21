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
     * @return ZoneRecord[]
     */
    public function getCreatedRecords(): array
    {
        return $this->createdRecords;
    }
}
