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
    public function createRecord($zone, CloudFlareAuthentication $authentication, ZoneRecord $record)
    {
        $this->decoratedClient->createRecord($zone, $authentication, $record);

        $this->createdRecords[] = $record;
    }

    /**
     * @return ZoneRecord[]
     */
    public function getCreatedRecords(): array
    {
        return $this->createdRecords;
    }
}
