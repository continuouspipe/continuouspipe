<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

class CloudFlareApiClient implements CloudFlareClient
{
    /**
     * @var AuthenticatedCloudFlareClientFactory
     */
    private $authenticatedCloudFlareClientFactory;

    /**
     * @param AuthenticatedCloudFlareClientFactory $authenticatedCloudFlareClientFactory
     */
    public function __construct(AuthenticatedCloudFlareClientFactory $authenticatedCloudFlareClientFactory)
    {
        $this->authenticatedCloudFlareClientFactory = $authenticatedCloudFlareClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record) : string
    {
        try {
            $response = $this->authenticatedCloudFlareClientFactory->dns($authentication)->create($zone, $record->getType(), $record->getHostname(), $record->getAddress());

            if (!isset($response->result->id)) {
                throw new CloudFlareException('The response from CloudFlare wasn\'t matching expected response');
            }

            return $response->result->id;
        } catch (\Exception $e) {
            throw new CloudFlareException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(string $zone, CloudFlareAuthentication $authentication, string $recordIdentifier)
    {
        try {
            $this->authenticatedCloudFlareClientFactory->dns($authentication)->delete_record($zone, $recordIdentifier);
        } catch (\Exception $e) {
            throw new CloudFlareException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
