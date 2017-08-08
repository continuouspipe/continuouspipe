<?php

namespace ContinuousPipe\HttpLabs\Endpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointException;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicEndpointTransformer;
use ContinuousPipe\HttpLabs\Authentication;
use ContinuousPipe\HttpLabs\Client\HttpLabsClient;
use ContinuousPipe\HttpLabs\Client\HttpLabsException;
use ContinuousPipe\HttpLabs\Client\Stack;
use ContinuousPipe\HttpLabs\Encryption\EncryptedAuthentication;
use ContinuousPipe\HttpLabs\Encryption\EncryptionNamespace;
use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Security\Encryption\Vault;
use Kubernetes\Client\Model\Annotation;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Repository\ServiceRepository;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class HttpLabsEndpointTransformer implements PublicEndpointTransformer
{
    const HTTPLABS_ANNOTATION = 'com.continuouspipe.io.httplabs.stack';
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
    /**
     * @var Vault
     */
    private $vault;

    public function __construct(
        HttpLabsClient $httpLabsClient,
        DeploymentClientFactory $deploymentClientFactory,
        LoggerFactory $loggerFactory,
        LoggerInterface $logger,
        Vault $vault
    ) {
        $this->httpLabsClient = $httpLabsClient;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
        $this->deploymentClientFactory = $deploymentClientFactory;
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
        if (null === ($httpLabsConfiguration = $endpointConfiguration->getHttpLabs())) {
            return $publicEndpoint;
        }

        if (!$object instanceof Service) {
            $this->logger->warning(
                'Unable to apply HttpLabs transformation on such object',
                ['object' => $object]
            );

            return $publicEndpoint;
        }

        $serviceRepository = $this->deploymentClientFactory->get($deploymentContext)->getServiceRepository();
        $logger = $this->loggerFactory->from($deploymentContext->getLog())
            ->child(new Text('Configuring the HttpLabs stack for endpoint ' . $publicEndpoint->getName()))
            ->updateStatus(Log::RUNNING);

        $metadata = $this->configureStack(
            $deploymentContext,
            $publicEndpoint,
            $this->refreshService($object, $serviceRepository),
            $httpLabsConfiguration,
            $serviceRepository,
            $logger
        );

        $logger->child(new Text('Stack configured with the address: ' . $metadata['stack_address']));
        $logger->updateStatus(Log::SUCCESS);

        return $publicEndpoint->withAddress($metadata['stack_address']);
    }

    private function configureStack(
        DeploymentContext $deploymentContext,
        PublicEndpoint $publicEndpoint,
        Service $service,
        Endpoint\HttpLabs $httpLabsConfiguration,
        ServiceRepository $serviceRepository,
        Logger $logger
    ): array {
        try {
            $httpLabsAnnotation = $service->getMetadata()->getAnnotationList()->get(
                self::HTTPLABS_ANNOTATION
            );

            if (null !== $httpLabsAnnotation) {
                $currentMetadata = \GuzzleHttp\json_decode($httpLabsAnnotation->getValue(), true);
                $metadata = $this->updateStack($publicEndpoint, $httpLabsConfiguration, $currentMetadata);

                if ($currentMetadata == $metadata) {
                    return $metadata;
                }
            } else {
                $stack = $this->createStack($deploymentContext, $publicEndpoint, $httpLabsConfiguration);
                $metadata = $this->createMetadata($stack, $httpLabsConfiguration);
            }

            $this->annotateService($serviceRepository, $service, $metadata);

            return $metadata;
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

    private function refreshService(Service $object, ServiceRepository $serviceRepository): Service
    {
        return $serviceRepository->findOneByName($object->getMetadata()->getName());
    }

    private function updateStack(PublicEndpoint $publicEndpoint, Endpoint\HttpLabs $httpLabsConfiguration, $metadata)
    {
        if (null !== $incoming = $httpLabsConfiguration->getIncoming()) {
            $metadata['stack_address'] = $incoming;
        }

        $this->httpLabsClient->updateStack(
            $httpLabsConfiguration->getApiKey(),
            $httpLabsConfiguration->getProjectIdentifier(),
            $metadata['stack_identifier'],
            $this->getBackendAddress($publicEndpoint),
            $httpLabsConfiguration->getMiddlewares(),
            $incoming
        );
        return $metadata;
    }

    private function createStack(
        DeploymentContext $deploymentContext,
        PublicEndpoint $publicEndpoint,
        Endpoint\HttpLabs $httpLabsConfiguration
    ): Stack {
        return $this->httpLabsClient->createStack(
            $httpLabsConfiguration->getApiKey(),
            $httpLabsConfiguration->getProjectIdentifier(),
            $deploymentContext->getEnvironment()->getName(),
            $this->getBackendAddress($publicEndpoint),
            $httpLabsConfiguration->getMiddlewares(),
            $httpLabsConfiguration->getIncoming()
        );
    }

    private function encryptAuthentication(Stack $stack, Endpoint\HttpLabs $httpLabsConfiguration)
    {
        return (new EncryptedAuthentication(
            $this->vault,
            EncryptionNamespace::from($stack->getIdentifier())
        ))->encrypt(new Authentication($httpLabsConfiguration->getApiKey()));
    }

    private function createMetadata($stack, $httpLabsConfiguration)
    {
        $metadata = [
            'stack_identifier' => $stack->getIdentifier(),
            'stack_address' => $this->getStackAddress($stack),
            'encrypted_authentication' => $this->encryptAuthentication($stack, $httpLabsConfiguration),
        ];
        return $metadata;
    }

    private function annotateService(ServiceRepository $serviceRepository, Service $service, array $metadata)
    {
        $serviceRepository->annotate(
            $service->getMetadata()->getName(),
            KeyValueObjectList::fromAssociativeArray(
                [
                    self::HTTPLABS_ANNOTATION => \GuzzleHttp\json_encode($metadata),
                ],
                Annotation::class
            )
        );
    }
}
