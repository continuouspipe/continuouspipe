<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicEndpointTransformer;
use ContinuousPipe\CloudFlare\Encryption\EncryptedAuthentication;
use ContinuousPipe\CloudFlare\Encryption\EncryptionNamespace;
use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Security\Encryption\Vault;
use Kubernetes\Client\Model\Annotation;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class CloudFlareEndpointTransformer implements PublicEndpointTransformer
{
    /**
     * @var CloudFlareClient
     */
    private $cloudFlareClient;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var DeploymentClientFactory
     */
    private $deploymentClientFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Vault
     */
    private $vault;

    public function __construct(
        CloudFlareClient $cloudFlareClient,
        LoggerFactory $loggerFactory,
        DeploymentClientFactory $deploymentClientFactory,
        LoggerInterface $logger,
        Vault $vault
    ) {
        $this->cloudFlareClient = $cloudFlareClient;
        $this->loggerFactory = $loggerFactory;
        $this->deploymentClientFactory = $deploymentClientFactory;
        $this->logger = $logger;
        $this->vault = $vault;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(
        DeploymentContext $deploymentContext,
        PublicEndpoint $publicEndpoint,
        Endpoint $endpointConfiguration,
        KubernetesObject $object
    ): PublicEndpoint {
        if (null === ($cloudFlareZone = $endpointConfiguration->getCloudFlareZone())) {
            return $publicEndpoint;
        }

        if (!$object instanceof Service) {
            $this->logger->warning('Unable to apply CloudFlare transformation on such object', [
                'object' => $object,
            ]);

            return $publicEndpoint;
        }

        // Refresh the service with the existing values
        $serviceRepository = $this->deploymentClientFactory->get($deploymentContext)->getServiceRepository();
        $service = $serviceRepository->findOneByName($object->getMetadata()->getName());

        $cloudFlareAnnotation = $service->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.cloudflare.zone');
        if (null !== $cloudFlareAnnotation) {
            $cloudFlareMetadata = \GuzzleHttp\json_decode($cloudFlareAnnotation->getValue(), true);
        } else {
            $recordAddress = $publicEndpoint->getAddress();
            $recordType = $this->getRecordTypeFromAddress($recordAddress);
            $recordName = $deploymentContext->getEnvironment()->getName() . $cloudFlareZone->getRecordSuffix();

            $logger = $this->loggerFactory->from($deploymentContext->getLog())
                ->child(new Text('Creating CloudFlare DNS record for endpoint ' . $publicEndpoint->getName()));

            $logger->updateStatus(Log::RUNNING);

            try {
                $identifier = $this->cloudFlareClient->createRecord(
                    $cloudFlareZone->getZoneIdentifier(),
                    $cloudFlareZone->getAuthentication(),
                    new ZoneRecord(
                        $recordName,
                        $recordType,
                        $recordAddress
                    )
                );

                $encryptedAuthentication = new EncryptedAuthentication(
                    $this->vault,
                    EncryptionNamespace::from($cloudFlareZone->getZoneIdentifier(), $identifier)
                );

                $cloudFlareMetadata = [
                    'record_name' => $recordName,
                    'record_identifier' => $identifier,
                    'zone_identifier' => $cloudFlareZone->getZoneIdentifier(),
                    'encrypted_authentication' => $encryptedAuthentication->encrypt($cloudFlareZone->getAuthentication()),
                ];

                $logger->child(new Text('Created zone record: ' . $recordName));
                $logger->updateStatus(Log::SUCCESS);

                $this->deploymentClientFactory->get($deploymentContext)->getServiceRepository()->annotate(
                    $service->getMetadata()->getName(),
                    KeyValueObjectList::fromAssociativeArray([
                        'com.continuouspipe.io.cloudflare.zone' => \GuzzleHttp\json_encode($cloudFlareMetadata),
                    ], Annotation::class)
                );
            } catch (\Exception $e) {
                $logger->child(new Text('Error: ' . $e->getMessage()));
                $logger->updateStatus(Log::FAILURE);

                return $publicEndpoint;
            }
        }

        return $publicEndpoint->withAddress($cloudFlareMetadata['record_name']);
    }

    /**
     * @param string $recordAddress
     *
     * @return string
     */
    private function getRecordTypeFromAddress(string $recordAddress)
    {
        if (filter_var($recordAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'A';
        } elseif (filter_var($recordAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'AAAA';
        }

        return 'CNAME';
    }
}
