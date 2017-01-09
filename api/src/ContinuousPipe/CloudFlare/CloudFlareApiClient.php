<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;
use Cloudflare\Zone\Dns;

class CloudFlareApiClient implements CloudFlareClient
{
    /**
     * {@inheritdoc}
     */
    public function createRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record)
    {
        try {
            $dns = new Dns($authentication->getEmail(), $authentication->getApiKey());
            $dns->create($zone, $record->getType(), $record->getHostname(), $record->getAddress());
        } catch (\Exception $e) {
            throw new CloudFlareException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
