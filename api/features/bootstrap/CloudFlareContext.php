<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\CloudFlare\TraceableCloudFlareClient;
use ContinuousPipe\CloudFlare\ZoneRecord;

class CloudFlareContext implements Context
{
    /**
     * @var TraceableCloudFlareClient
     */
    private $traceableCloudFlareClient;

    /**
     * @param TraceableCloudFlareClient $traceableCloudFlareClient
     */
    public function __construct(TraceableCloudFlareClient $traceableCloudFlareClient)
    {
        $this->traceableCloudFlareClient = $traceableCloudFlareClient;
    }

    /**
     * @Then the CloudFlare zone :name should have been created with the type :type and the address :address
     */
    public function theCloudflareZoneShouldHaveBeenCreatedWithTheTypeAAndTheAddress($name, $type, $address)
    {
        $matchingRecords = array_filter($this->traceableCloudFlareClient->getCreatedRecords(), function(ZoneRecord $record) use ($name, $type, $address) {
            return $record->getHostname() == $name && $record->getType() == $type && $record->getAddress() == $address;
        });

        if (count($matchingRecords) == 0) {
            throw new \RuntimeException('No matching created record found');
        }
    }

    /**
     * @Then the CloudFlare zone :name should have been created as proxied
     */
    public function theCloudflareZoneShouldHaveBeenCreatedAsProxied($name)
    {
        /** @var ZoneRecord[] $matchingRecords */
        $matchingRecords = array_filter($this->traceableCloudFlareClient->getCreatedRecords(), function(ZoneRecord $record) use ($name) {
            return $record->getHostname() == $name;
        });

        if (count($matchingRecords) == 0) {
            throw new \RuntimeException('No matching created record found');
        }

        foreach ($matchingRecords as $record) {
            if (!$record->isProxied()) {
                throw new \RuntimeException('The record should be proxied but is not');
            }
        }
    }

    /**
     * @Then the CloudFlare record :record of the zone :zone should have been deleted
     */
    public function theCloudFlareRecordOfTheZoneShouldHaveBeenDeleted($record, $zone)
    {
        $matchingRecords = array_filter($this->traceableCloudFlareClient->getDeletedRecords(), function(string $deletedRecord) use ($record) {
            return $deletedRecord == $record;
        });

        if (count($matchingRecords) == 0) {
            throw new \RuntimeException('No matching deleted record found');
        }
    }
}
