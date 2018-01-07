<?php

namespace ContinuousPipe\Pipe\Kubernetes\Handler;

use ContinuousPipe\Pipe\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Pipe\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Pipe\Kubernetes\ObjectDeployer\ObjectDeployer;
use ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\PublicEndpointObjectVoter;
use ContinuousPipe\Pipe\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
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
     * @var PublicEndpointObjectVoter
     */
    private $publicServiceVoter;
    /**
     * @var ObjectDeployer
     */
    private $objectDeployer;

    /**
     * @param EnvironmentTransformer    $environmentTransformer
     * @param DeploymentClientFactory   $clientFactory
     * @param MessageBus                $eventBus
     * @param LoggerFactory             $loggerFactory
     * @param PublicEndpointObjectVoter $publicServiceVoter
     * @param ObjectDeployer            $objectDeployer
     */
    public function __construct(
        EnvironmentTransformer $environmentTransformer,
        DeploymentClientFactory $clientFactory,
        MessageBus $eventBus,
        LoggerFactory $loggerFactory,
        PublicEndpointObjectVoter $publicServiceVoter,
        ObjectDeployer $objectDeployer
    ) {
        $this->environmentTransformer = $environmentTransformer;
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->publicServiceVoter = $publicServiceVoter;
        $this->objectDeployer = $objectDeployer;
    }

    /**
     * @param CreatePublicEndpointsCommand $command
     */
    public function handle(CreatePublicEndpointsCommand $command)
    {
        $context = $command->getContext();

        $logger = $this->loggerFactory->from($context->getLog());

        try {
            $objects = $this->getPublicEndpointObjects($context->getEnvironment());
            $status = $this->createPublicEndpointObjects($this->clientFactory->get($context), $objects);

            $this->eventBus->handle(new PublicServicesCreated($context, $status));
        } catch (\Exception $e) {
            $logger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);

            $this->eventBus->handle(new DeploymentFailed($context));
        }
    }

    /**
     * @param NamespaceClient    $namespaceClient
     * @param KubernetesObject[] $objects
     *
     * @return ComponentCreationStatus
     */
    private function createPublicEndpointObjects(NamespaceClient $namespaceClient, array $objects)
    {
        $status = new ComponentCreationStatus();

        foreach ($objects as $object) {
            $status->merge(
                $this->objectDeployer->deploy($namespaceClient, $object)
            );
        }

        return $status;
    }

    /**
     * @param Environment $environment
     *
     * @return KubernetesObject[]
     */
    private function getPublicEndpointObjects(Environment $environment)
    {
        $namespaceObjects = $this->environmentTransformer->getElementListFromEnvironment($environment);
        $objects = array_filter($namespaceObjects, function (KubernetesObject $object) {
            return $this->publicServiceVoter->isPublicEndpointObject($object);
        });

        return array_values($objects);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof Kubernetes;
    }
}
