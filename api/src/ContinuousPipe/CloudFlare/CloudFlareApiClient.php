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
            $response = $dns->create($zone, $record->getType(), $record->getHostname(), $record->getAddress());

            if (!isset($response['result']['id'])) {
                throw new CloudFlareException('The response from CloudFlare wasn\'t matching expected response');
            }

            return $response['result']['id'];
        } catch (\Exception $e) {
            throw new CloudFlareException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
