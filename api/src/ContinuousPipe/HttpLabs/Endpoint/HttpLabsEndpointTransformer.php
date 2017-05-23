<?php

namespace ContinuousPipe\HttpLabs\Endpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointException;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicEndpointTransformer;
use ContinuousPipe\HttpLabs\Client\HttpLabsClient;
use ContinuousPipe\HttpLabs\Client\HttpLabsException;
use ContinuousPipe\HttpLabs\Client\Stack;
use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Annotation;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class HttpLabsEndpointTransformer implements PublicEndpointTransformer
{
    /**
     * @var HttpLabsClient
     */
    private $httpLabsClient;
    /**
     * @var DeploymentClientFactory
     */
    private $deploymentClientFactory;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HttpLabsClient $httpLabsClient,
        DeploymentClientFactory $deploymentClientFactory,
        LoggerFactory $loggerFactory,
        LoggerInterface $logger
    ) {
        $this->httpLabsClient = $httpLabsClient;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
        $this->deploymentClientFactory = $deploymentClientFactory;
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
        if (null === ($httpLabsConfiguration = $endpointConfiguration->getHttpLabs())) {
            return $publicEndpoint;
        }

        if (!$object instanceof Service) {
            $this->logger->warning(
                'Unable to apply HttpLabs transformation on such object',
                [
                    'object' => $object,
                ]
            );

            return $publicEndpoint;
        }

        // Refresh the service with the existing values
        $serviceRepository = $this->deploymentClientFactory->get($deploymentContext)->getServiceRepository();
        $service = $serviceRepository->findOneByName($object->getMetadata()->getName());

        $logger = $this->loggerFactory->from($deploymentContext->getLog())
            ->child(new Text('Configuring the HttpLabs stack for endpoint ' . $publicEndpoint->getName()))
            ->updateStatus(Log::RUNNING);

        try {
            $httpLabsAnnotation = $service->getMetadata()->getAnnotationList()->get(
                'com.continuouspipe.io.httplabs.stack'
            );
            if (null !== $httpLabsAnnotation) {
                $metadata = \GuzzleHttp\json_decode($httpLabsAnnotation->getValue(), true);

                $this->httpLabsClient->updateStack(
                    $httpLabsConfiguration->getApiKey(),
                    $metadata['stack_identifier'],
                    $this->getBackendAddress($publicEndpoint),
                    $httpLabsConfiguration->getMiddlewares()
                );
            } else {
                $stack = $this->httpLabsClient->createStack(
                    $httpLabsConfiguration->getApiKey(),
                    $httpLabsConfiguration->getProjectIdentifier(),
                    $deploymentContext->getEnvironment()->getName(),
                    $this->getBackendAddress($publicEndpoint),
                    $httpLabsConfiguration->getMiddlewares()
                );

                $metadata = [
                    'stack_identifier' => $stack->getIdentifier(),
                    'stack_address' => $this->getStackAddress($stack),
                ];

                $serviceRepository->annotate(
                    $service->getMetadata()->getName(),
                    KeyValueObjectList::fromAssociativeArray(
                        [
                            'com.continuouspipe.io.httplabs.stack' => \GuzzleHttp\json_encode($metadata),
                        ],
                        Annotation::class
                    )
                );
            }
        } catch (\Throwable $e) {
            $logger->updateStatus(Log::FAILURE);

            $this->logger->warning(
                'Something went wrong while configuring the HttpLabs stack',
                [
                    'exception' => $e,
                ]
            );

            throw new EndpointException(
                'Something went wrong while configuring the HttpLabs stack: ' . $e->getMessage()
            );
        }

        $logger->child(new Text('Stack configured with the address: ' . $metadata['stack_address']));
        $logger->updateStatus(Log::SUCCESS);

        return $publicEndpoint->withAddress($metadata['stack_address']);
    }

    private function getStackAddress(Stack $stack) : string
    {
        if (false === ($address = parse_url($stack->getUrl(), PHP_URL_HOST))) {
            throw new HttpLabsException(sprintf('Can\'t get the address from the URL "%s"', $stack->getUrl()));
        }

        return $address;
    }

    private function getBackendAddress(PublicEndpoint $endpoint) : string
    {
        if ($this->hasPort($endpoint, 443)) {
            return 'https://' . $endpoint->getAddress();
        } elseif ($this->hasPort($endpoint, 80)) {
            return 'http://' . $endpoint->getAddress();
        }

        throw new HttpLabsException(
            'The HttpLabs proxy can be created only for services exposing the HTTP (80) or HTTPS (443) port'
        );
    }

    private function hasPort(PublicEndpoint $endpoint, int $port) : bool
    {
        foreach ($endpoint->getPorts() as $endpointPort) {
            if ($endpointPort->getNumber() == $port) {
                return true;
            }
        }

        return false;
    }
}
