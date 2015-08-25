<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;
use SimpleBus\Message\Bus\MessageBus;

class CreatePublicEndpointsHandler
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
     * @param EnvironmentTransformer  $environmentTransformer
     * @param DeploymentClientFactory $clientFactory
     * @param MessageBus              $eventBus
     */
    public function __construct(EnvironmentTransformer $environmentTransformer, DeploymentClientFactory $clientFactory, MessageBus $eventBus)
    {
        $this->environmentTransformer = $environmentTransformer;
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
    }

    /**
     * @param CreatePublicEndpointsCommand $command
     */
    public function handle(CreatePublicEndpointsCommand $command)
    {
        $context = $command->getContext();
        if (!$this->shouldHandle($context)) {
            return;
        }

        $services = $this->getPublicServices($context->getEnvironment());
        $client = $this->clientFactory->get($context);
        $serviceRepository = $client->getServiceRepository();

        $createdServices = [];
        foreach ($services as $service) {
            $serviceName = $service->getMetadata()->getName();

            try {
                $serviceRepository->findOneByName($serviceName);
                $createdServices[] = $serviceRepository->update($service);
            } catch (ServiceNotFound $e) {
                $createdServices[] = $serviceRepository->create($service);
            }
        }

        $this->eventBus->handle(new PublicServicesCreated($context, $createdServices));
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
     * @param DeploymentContext $context
     *
     * @return bool
     */
    private function shouldHandle(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }
}
