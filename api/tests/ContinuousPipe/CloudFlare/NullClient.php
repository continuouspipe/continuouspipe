<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

class NullClient implements CloudFlareClient
{
    /**
     * {@inheritdoc}
     */
    public function createOrUpdateRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record) : string
    {
        return '1234';
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(string $zone, CloudFlareAuthentication $authentication, string $recordIdentifier)
    {
    }
}
