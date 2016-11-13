<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\PublicEndpointsCreated;

use ContinuousPipe\CloudFlare\CloudFlareClient;
use ContinuousPipe\CloudFlare\CloudFlareException;
use ContinuousPipe\CloudFlare\ZoneRecord;
use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
use ContinuousPipe\Pipe\Event\PublicEndpointsReady;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class ApplyPublicEndpointsTransformations
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var CloudFlareClient
     */
    private $cloudFlareClient;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus       $eventBus
     * @param CloudFlareClient $cloudFlareClient
     * @param LoggerFactory    $loggerFactory
     */
    public function __construct(MessageBus $eventBus, CloudFlareClient $cloudFlareClient, LoggerFactory $loggerFactory)
    {
        $this->eventBus = $eventBus;
        $this->cloudFlareClient = $cloudFlareClient;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param PublicEndpointsCreated $event
     */
    public function notify(PublicEndpointsCreated $event)
    {
        $deploymentContext = $event->getDeploymentContext();
        $deployment = $deploymentContext->getDeployment();

        $endpointsConfiguration = [];
        foreach ($deployment->getRequest()->getSpecification()->getComponents() as $component) {
            foreach ($component->getEndpoints() as $endpoint) {
                $endpointsConfiguration[$endpoint->getName()] = $endpoint;
            }
        }

        $publicEndpoints = array_map(function (PublicEndpoint $publicEndpoint) use ($deploymentContext, $endpointsConfiguration) {
            if (!array_key_exists($publicEndpoint->getName(), $endpointsConfiguration)) {
                return $publicEndpoint;
            }

            return $this->applyEndpointTransformation($deploymentContext, $publicEndpoint, $endpointsConfiguration[$publicEndpoint->getName()]);
        }, $event->getEndpoints());

        $this->eventBus->handle(new PublicEndpointsReady($event->getDeploymentContext(), $publicEndpoints));
    }

    /**
     * @param DeploymentContext $deploymentContext
     * @param PublicEndpoint    $publicEndpoint
     * @param Endpoint          $configuration
     *
     * @return PublicEndpoint
     */
    private function applyEndpointTransformation(DeploymentContext $deploymentContext, PublicEndpoint $publicEndpoint, Endpoint $configuration)
    {
        if (null !== ($cloudFlareZone = $configuration->getCloudFlareZone())) {
            $publicEndpoint = $this->applyCloudFlareTransformation($deploymentContext, $publicEndpoint, $cloudFlareZone);
        }

        return $publicEndpoint;
    }

    /**
     * @param DeploymentContext       $deploymentContext
     * @param PublicEndpoint          $publicEndpoint
     * @param Endpoint\CloudFlareZone $cloudFlareZone
     *
     * @return PublicEndpoint
     */
    private function applyCloudFlareTransformation(DeploymentContext $deploymentContext, PublicEndpoint $publicEndpoint, Endpoint\CloudFlareZone $cloudFlareZone)
    {
        $recordAddress = $publicEndpoint->getAddress();
        $recordType = $this->getRecordTypeFromAddress($recordAddress);
        $recordName = $deploymentContext->getEnvironment()->getName().$cloudFlareZone->getRecordSuffix();

        $logger = $this->loggerFactory->from($deploymentContext->getLog())
            ->child(new Text('Creating CloudFlare DNS record for endpoint '.$publicEndpoint->getName()));

        $logger->updateStatus(Log::RUNNING);

        try {
            $this->cloudFlareClient->createRecord(
                $cloudFlareZone->getZoneIdentifier(),
                $cloudFlareZone->getAuthentication(),
                new ZoneRecord(
                    $recordName,
                    $recordType,
                    $recordAddress
                )
            );

            $logger->child(new Text('Created zone: '.$recordName));
            $logger->updateStatus(Log::SUCCESS);

            $publicEndpoint = new PublicEndpoint(
                $publicEndpoint->getName(),
                $recordName,
                $publicEndpoint->getPorts()
            );
        } catch (CloudFlareException $e) {
            $logger->child(new Text('Error: '.$e->getMessage()));
            $logger->updateStatus(Log::FAILURE);
        }

        return $publicEndpoint;
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
