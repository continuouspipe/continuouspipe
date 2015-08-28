<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use Kubernetes\Client\Exception\ClientError;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;
use Kubernetes\Client\Repository\ServiceRepository;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class CreatePublicEndpointsHandler implements DeploymentHandler
{
    /**
     * @var EnvironmentTransformer
     */
    private $environmentTransformer;

    /**
     * @var DeploymentClientFactory
     */
    private $clientFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param EnvironmentTransformer $environmentTransformer
     * @param DeploymentClientFactory $clientFactory
     * @param MessageBus $eventBus
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(EnvironmentTransformer $environmentTransformer, DeploymentClientFactory $clientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory)
    {
        $this->environmentTransformer = $environmentTransformer;
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param CreatePublicEndpointsCommand $command
     */
    public function handle(CreatePublicEndpointsCommand $command)
    {
        $context = $command->getContext();
        $services = $this->getPublicServices($context->getEnvironment());
        $serviceRepository = $this->clientFactory->get($context)->getServiceRepository();

        $log = $this->loggerFactory->from($context->getLog())->append(new Text('Create services for public endpoints'));
        $logger = $this->loggerFactory->from($log);
        $logger->start();

        try {
            $createdServices = $this->createServices($serviceRepository, $services, $logger);
        } catch (ClientError $e) {
            $logger->append(new Text('Error: '.$e->getMessage()));
            $logger->failure();

            $this->eventBus->handle(new DeploymentFailed($context->getDeployment()->getUuid()));

            throw $e;
        }

        $logger->success();

        $this->eventBus->handle(new PublicServicesCreated($context, $createdServices));
    }

    /**
     * @param ServiceRepository $serviceRepository
     * @param Service[] $services
     * @param Logger $logger
     * @return \Kubernetes\Client\Model\Service[]
     */
    private function createServices(ServiceRepository $serviceRepository, array $services, Logger $logger)
    {
        $createdServices = [];
        foreach ($services as $service) {
            $serviceName = $service->getMetadata()->getName();

            if ($serviceRepository->exists($serviceName)) {
                $createdServices[] = $serviceRepository->update($service);
                $logger->append(new Text(sprintf('Updated service "%s"', $serviceName)));
            } else {
                $createdServices[] = $serviceRepository->create($service);
                $logger->append(new Text(sprintf('Created service "%s"', $serviceName)));
            }
        }

        return $createdServices;
    }

    /**
     * @param Environment $environment
     *
     * @return Service[]
     */
    private function getPublicServices(Environment $environment)
    {
        $namespaceObjects = $this->environmentTransformer->getElementListFromEnvironment($environment);
        $publicServices = array_filter($namespaceObjects, function (KubernetesObject $object) {
            return $this->isAPublicService($object);
        });

        return array_values($publicServices);
    }

    /**
     * @param KubernetesObject $object
     *
     * @return bool
     */
    private function isAPublicService(KubernetesObject $object)
    {
        return $object instanceof Service && $object->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }
}
