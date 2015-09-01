<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicServiceVoter;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use Kubernetes\Client\Exception\ClientError;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
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
     * @var PublicServiceVoter
     */
    private $publicServiceVoter;

    /**
     * @param EnvironmentTransformer  $environmentTransformer
     * @param DeploymentClientFactory $clientFactory
     * @param MessageBus              $eventBus
     * @param LoggerFactory           $loggerFactory
     * @param PublicServiceVoter      $publicServiceVoter
     */
    public function __construct(EnvironmentTransformer $environmentTransformer, DeploymentClientFactory $clientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, PublicServiceVoter $publicServiceVoter)
    {
        $this->environmentTransformer = $environmentTransformer;
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->publicServiceVoter = $publicServiceVoter;
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
     * @param Service[]         $services
     * @param Logger            $logger
     *
     * @return \Kubernetes\Client\Model\Service[]
     */
    private function createServices(ServiceRepository $serviceRepository, array $services, Logger $logger)
    {
        $publicServices = [];
        foreach ($services as $service) {
            $serviceName = $service->getMetadata()->getName();

            if ($serviceRepository->exists($serviceName)) {
                if (!$this->serviceNeedsToBeUpdated($serviceRepository, $service)) {
                    $publicServices[] = $serviceRepository->findOneByName($serviceName);

                    continue;
                }

                $serviceRepository->delete($service);
                $logger->append(new Text(sprintf('Deleted service "%s"', $serviceName)));
            }

            $publicServices[] = $serviceRepository->create($service);
            $logger->append(new Text(sprintf('Created service "%s"', $serviceName)));
        }

        return $publicServices;
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
            return $this->publicServiceVoter->isAPublicService($object);
        });

        return array_values($publicServices);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }

    /**
     * @param ServiceRepository $repository
     * @param Service $service
     *
     * @return bool
     */
    private function serviceNeedsToBeUpdated(ServiceRepository $repository, Service $service)
    {
        $existingService = $repository->findOneByName($service->getMetadata()->getName());
        $existingSelector = $existingService->getSpecification()->getSelector();
        $newSelector = $service->getSpecification()->getSelector();

        return $existingSelector != $newSelector;
    }
}
