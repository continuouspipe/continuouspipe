<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentException;
use ContinuousPipe\Adapter\Kubernetes\Event\AfterCreatingComponent;
use ContinuousPipe\Adapter\Kubernetes\Event\BeforeCreatingComponent;
use ContinuousPipe\Adapter\Kubernetes\ObjectDeployer\ObjectDeployer;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicEndpointObjectVoter;
use ContinuousPipe\Adapter\Kubernetes\Transformer\ComponentTransformer;
use ContinuousPipe\Adapter\Kubernetes\Transformer\TransformationException;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Command\CreateComponentsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Exception\Exception as KubernetesException;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateComponentsHandler implements DeploymentHandler
{
    /**
     * @var ComponentTransformer
     */
    private $componentTransformer;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ObjectDeployer
     */
    private $objectDeployer;

    /**
     * @param ComponentTransformer      $componentTransformer
     * @param DeploymentClientFactory   $clientFactory
     * @param MessageBus                $eventBus
     * @param LoggerFactory             $loggerFactory
     * @param PublicEndpointObjectVoter $publicServiceVoter
     * @param EventDispatcherInterface  $eventDispatcher
     * @param ObjectDeployer            $objectDeployer
     */
    public function __construct(ComponentTransformer $componentTransformer, DeploymentClientFactory $clientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, PublicEndpointObjectVoter $publicServiceVoter, EventDispatcherInterface $eventDispatcher, ObjectDeployer $objectDeployer)
    {
        $this->componentTransformer = $componentTransformer;
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->publicServiceVoter = $publicServiceVoter;
        $this->eventDispatcher = $eventDispatcher;
        $this->objectDeployer = $objectDeployer;
    }

    /**
     * @param CreateComponentsCommand $command
     */
    public function handle(CreateComponentsCommand $command)
    {
        $context = $command->getContext();
        $client = $this->clientFactory->get($context);
        $environment = $context->getEnvironment();

        try {
            $componentStatus = $this->createComponents($client, $context, $environment);

            $this->eventBus->handle(new ComponentsCreated($context, $componentStatus));
        } catch (ComponentException $e) {
            $logger = $this->loggerFactory->from($context->getLog());
            $logger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);

            $this->eventBus->handle(new DeploymentFailed($context));
        }
    }

    /**
     * Create the components and return the component statuses.
     *
     * @param NamespaceClient   $client
     * @param DeploymentContext $context
     * @param Environment       $environment
     *
     * @return array
     *
     * @throws ComponentException
     */
    private function createComponents(NamespaceClient $client, DeploymentContext $context, Environment $environment)
    {
        $componentStatus = [];
        foreach ($environment->getComponents() as $component) {
            try {
                $this->eventDispatcher->dispatch(BeforeCreatingComponent::NAME, new BeforeCreatingComponent(
                    $client, $context, $component
                ));

                $status = $this->createComponent($client, $component);
                $componentStatus[$component->getName()] = $status;

                $this->eventDispatcher->dispatch(AfterCreatingComponent::NAME, new AfterCreatingComponent(
                    $client, $context, $component, $status
                ));
            } catch (TransformationException $e) {
                throw new ComponentException(sprintf(
                    'Unable to create the component "%s": %s',
                    $component->getName(),
                    $e->getMessage()
                ));
            } catch (KubernetesException $e) {
                throw new ComponentException(sprintf(
                    'An error appeared while creating the component "%s": %s',
                    $component->getName(),
                    $e->getMessage()
                ));
            }
        }

        return $componentStatus;
    }

    /**
     * Create a component.
     *
     * @param NamespaceClient $client
     * @param Component       $component
     *
     * @return ComponentCreationStatus
     */
    private function createComponent(NamespaceClient $client, Component $component)
    {
        $objects = $this->componentTransformer->getElementListFromComponent($component);
        $creationStatus = new ComponentCreationStatus();

        foreach ($objects as $object) {
            if (!$this->publicServiceVoter->isPublicEndpointObject($object)) {
                $creationStatus->merge(
                    $this->objectDeployer->deploy($client, $object, $component->getDeploymentStrategy())
                );
            }
        }

        return $creationStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof Kubernetes;
    }
}
