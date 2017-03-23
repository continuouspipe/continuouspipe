<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

interface CloudFlareClient
{
    /**
     * @param string $zone
     * @param CloudFlareAuthentication $authentication
     * @param ZoneRecord $record
     *
     * @throws CloudFlareException
     *
     * @return string
     */
    public function createRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record) : string;

    /**
     * @param string $zone
     * @param CloudFlareAuthentication $authentication
     * @param string $recordIdentifier
     *
     * @throws CloudFlareException
     */
    public function deleteRecord(string $zone, CloudFlareAuthentication $authentication, string $recordIdentifier);
}
