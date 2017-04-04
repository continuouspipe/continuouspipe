<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointException;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicEndpointTransformer;
use ContinuousPipe\CloudFlare\AnnotationManager\AnnotationManager;
use ContinuousPipe\CloudFlare\Encryption\EncryptedAuthentication;
use ContinuousPipe\CloudFlare\Encryption\EncryptionNamespace;
use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Security\Encryption\Vault;
use Kubernetes\Client\Model\IngressRule;
use Kubernetes\Client\Model\KubernetesObject;
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
    /**
     * @var AnnotationManager
     */
    private $annotationManager;

    public function __construct(
        CloudFlareClient $cloudFlareClient,
        LoggerFactory $loggerFactory,
        DeploymentClientFactory $deploymentClientFactory,
        LoggerInterface $logger,
        Vault $vault,
        AnnotationManager $annotationManager
    ) {
        $this->cloudFlareClient = $cloudFlareClient;
        $this->loggerFactory = $loggerFactory;
        $this->deploymentClientFactory = $deploymentClientFactory;
        $this->logger = $logger;
        $this->vault = $vault;
        $this->annotationManager = $annotationManager;
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

        if (null !== $cloudFlareZone->getHostname()) {
            $records = [$cloudFlareZone->getHostname()];
        } elseif (null !== $endpointConfiguration->getIngress() && 0 !== count($rules = $endpointConfiguration->getIngress()->getRules())) {
            $records = array_map(function (IngressRule $ingressRule) {
                return $ingressRule->getHost();
            }, $rules);
        } else {
            throw new EndpointException('Can\'t find which DNS record to create. You can set the `record_suffix` or some ingress hosts.');
        }

        if (null === ($recordAddress = $cloudFlareZone->getBackendAddress())) {
            $recordAddress = $publicEndpoint->getAddress();
        }
        
        $recordType = $this->getRecordTypeFromAddress($recordAddress);

        $logger = $this->loggerFactory->from($deploymentContext->getLog())
            ->child(new Text('Creating CloudFlare DNS record for endpoint ' . $publicEndpoint->getName()));

        $logger->updateStatus(Log::RUNNING);

        try {
            $cloudFlareMetadata = [];

            foreach ($records as $recordName) {
                $identifier = $this->cloudFlareClient->createOrUpdateRecord(
                    $cloudFlareZone->getZoneIdentifier(),
                    $cloudFlareZone->getAuthentication(),
                    new ZoneRecord(
                        $recordName,
                        $recordType,
                        $recordAddress,
                        $cloudFlareZone->getTtl(),
                        $cloudFlareZone->isProxied()
                    )
                );

                $encryptedAuthentication = new EncryptedAuthentication(
                    $this->vault,
                    EncryptionNamespace::from($cloudFlareZone->getZoneIdentifier(), $identifier)
                );

                $cloudFlareMetadata[] = [
                    'record_name' => $recordName,
                    'record_identifier' => $identifier,
                    'zone_identifier' => $cloudFlareZone->getZoneIdentifier(),
                    'encrypted_authentication' => $encryptedAuthentication->encrypt($cloudFlareZone->getAuthentication()),
                ];

                $logger->child(new Text('Created zone record: ' . $recordName));
            }

            $logger->updateStatus(Log::SUCCESS);

            $this->annotationManager->writeAnnotation($deploymentContext, $object, 'com.continuouspipe.io.cloudflare.records', \GuzzleHttp\json_encode($cloudFlareMetadata));
        } catch (\Throwable $e) {
            $this->logger->warning('Something went wrong while creating the CloudFlare zone', [
                'exception' => $e,
            ]);

            $logger->child(new Text('Error: ' . $e->getMessage()));
            $logger->updateStatus(Log::FAILURE);

            return $publicEndpoint;
        }

        return $publicEndpoint->withAddress(current($records));
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
