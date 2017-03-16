<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;
use Psr\Log\LoggerInterface;

class CloudFlareApiClient implements CloudFlareClient
{
    /**
     * @var AuthenticatedCloudFlareClientFactory
     */
    private $authenticatedCloudFlareClientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AuthenticatedCloudFlareClientFactory $authenticatedCloudFlareClientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(AuthenticatedCloudFlareClientFactory $authenticatedCloudFlareClientFactory, LoggerInterface $logger)
    {
        $this->authenticatedCloudFlareClientFactory = $authenticatedCloudFlareClientFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record) : string
    {
        try {
            $response = $this->authenticatedCloudFlareClientFactory->dns($authentication)->create($zone, $record->getType(), $record->getHostname(), $record->getAddress());
        } catch (\Exception $e) {
            throw new CloudFlareException($e->getMessage(), $e->getCode(), $e);
        }

        if (!isset($response->result->id)) {
            $this->logger->warning('CloudFlare response is not understandable', [
                'response' => \GuzzleHttp\json_encode($response),
            ]);

            throw new CloudFlareException('The response from CloudFlare wasn\'t matching expected response');
        }

        return $response->result->id;
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
