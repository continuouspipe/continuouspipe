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
}
