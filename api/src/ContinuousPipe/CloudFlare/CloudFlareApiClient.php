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
    public function createOrUpdateRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record) : string
    {
        try {
            $dns = $this->authenticatedCloudFlareClientFactory->dns($authentication);

            $response = $dns->list_records($zone, null, $record->getHostname());
            $existingRecord = isset($response->result) && is_array($response->result) ? reset($response->result) : false;

            if (false === $existingRecord) {
                $response = $dns->create(
                    $zone,
                    $record->getType(),
                    $record->getHostname(),
                    $record->getAddress(),
                    $record->getTtl() ?: 1,
                    $record->isProxied() ?: false
                );
            } else {
                $response = $dns->update(
                    $zone,
                    $existingRecord->id,
                    $record->getType(),
                    $record->getHostname(),
                    $record->getAddress(),
                    $record->getTtl() ?: 1,
                    $record->isProxied() ?: false
                );
            }

            if (!isset($response->result->id)) {
                $this->logger->warning('CloudFlare response is not understandable', [
                    'response' => \GuzzleHttp\json_encode($response),
                    'existing_record' => $existingRecord,
                ]);

                throw new CloudFlareException('The response from CloudFlare wasn\'t matching expected response');
            }
        } catch (\Exception $e) {
            throw new CloudFlareException($e->getMessage(), $e->getCode(), $e);
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
