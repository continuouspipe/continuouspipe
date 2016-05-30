<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentException;
use ContinuousPipe\Adapter\Kubernetes\Event\AfterCreatingComponent;
use ContinuousPipe\Adapter\Kubernetes\Event\BeforeCreatingComponent;
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
use Kubernetes\Client\Exception\ClientError;
use Kubernetes\Client\Exception\ObjectNotFound;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\ObjectRepository;
use Kubernetes\Client\Repository\WrappedObjectRepository;
use LogStream\Logger;
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
     * @param ComponentTransformer      $componentTransformer
     * @param DeploymentClientFactory   $clientFactory
     * @param MessageBus                $eventBus
     * @param LoggerFactory             $loggerFactory
     * @param PublicEndpointObjectVoter $publicServiceVoter
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(ComponentTransformer $componentTransformer, DeploymentClientFactory $clientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, PublicEndpointObjectVoter $publicServiceVoter, EventDispatcherInterface $eventDispatcher)
    {
        $this->componentTransformer = $componentTransformer;
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->publicServiceVoter = $publicServiceVoter;
        $this->eventDispatcher = $eventDispatcher;
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
            $logger->child(new Text($e->getMessage()));

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
        $logger = $this->loggerFactory->from($context->getLog());

        $componentStatus = [];
        foreach ($environment->getComponents() as $component) {
            try {
                $this->eventDispatcher->dispatch(BeforeCreatingComponent::NAME, new BeforeCreatingComponent(
                    $client, $context, $component
                ));

                $status = $this->createComponent($client, $logger, $component);
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
            } catch (ClientError $e) {
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
     * @param Logger          $logger
     * @param Component       $component
     *
     * @return ComponentCreationStatus
     */
    private function createComponent(NamespaceClient $client, Logger $logger, Component $component)
    {
        $objects = $this->componentTransformer->getElementListFromComponent($component);
        $creationStatus = new ComponentCreationStatus();

        foreach ($objects as $object) {
            if ($this->publicServiceVoter->isPublicEndpointObject($object)) {
                $logger->child(new Text('Ignoring the public service '.$this->getObjectTypeAndName($object)));

                continue;
            }

            $this->createObject($client, $logger, $creationStatus, $component, $object);
        }

        return $creationStatus;
    }

    /**
     * Create or update the object.
     *
     * @param NamespaceClient         $client
     * @param Logger                  $logger
     * @param ComponentCreationStatus $status
     * @param Component               $component
     * @param KubernetesObject        $object
     */
    private function createObject(NamespaceClient $client, Logger $logger, ComponentCreationStatus $status, Component $component, KubernetesObject $object)
    {
        $objectRepository = $this->getObjectRepository($client, $object);
        $objectName = $object->getMetadata()->getName();

        try {
            $existingObject = $objectRepository->findOneByName($objectName);
        } catch (ObjectNotFound $e) {
            $existingObject = null;
        }

        if ($existingObject !== null) {
            if (null !== ($deploymentStrategy = $component->getDeploymentStrategy()) && $deploymentStrategy->isLocked()) {
                $logger->child(new Text('NOT updated '.$this->getObjectTypeAndName($object).' because it is locked'));

                return;
            }

            if ($object instanceof ReplicationController) {
                // Keeps the number of replicas of the RC
                if ($object->getSpecification()->getReplicas() <= 0) {
                    $object->getSpecification()->setReplicas(
                        $existingObject->getSpecification()->getReplicas()
                    );
                }
            }

            $logger->child(new Text('Updating '.$this->getObjectTypeAndName($object)));
            $objectRepository->update($object);
            $status->addUpdated($object);

            if ($object instanceof ReplicationController) {
                // Has an extremely simple RC-update feature, we can delete matching RC's pods
                // Wait the "real" rolling-update feature
                // @link https://github.com/sroze/continuouspipe/issues/54
                $this->deleteReplicationControllerPods($client, $status, $object);
            }
        } else {
            if ($object instanceof ReplicationController) {
                if ($object->getSpecification()->getReplicas() <= 0) {
                    $object->getSpecification()->setReplicas(1);
                }
            }

            $logger->child(new Text('Creating '.$this->getObjectTypeAndName($object)));
            $objectRepository->create($object);
            $status->addCreated($object);
        }
    }

    /**
     * Get an abstract object repository.
     *
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     *
     * @return ObjectRepository
     */
    private function getObjectRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        if ($object instanceof Pod) {
            $repository = $namespaceClient->getPodRepository();
        } elseif ($object instanceof Service) {
            $repository = $namespaceClient->getServiceRepository();
        } elseif ($object instanceof ReplicationController) {
            $repository = $namespaceClient->getReplicationControllerRepository();
        } elseif ($object instanceof Ingress) {
            $repository = $namespaceClient->getIngressRepository();
        } elseif ($object instanceof Secret) {
            $repository = $namespaceClient->getSecretRepository();
        } else {
            throw new \RuntimeException(sprintf(
                'Unsupported object of type "%s"',
                get_class($object)
            ));
        }

        return new WrappedObjectRepository($repository);
    }

    /**
     * Delete RC's pods.
     *
     * That will force the replication controller to recreate them and pull the new image.
     *
     * @param NamespaceClient         $namespaceClient
     * @param ComponentCreationStatus $status
     * @param ReplicationController   $object
     */
    private function deleteReplicationControllerPods(NamespaceClient $namespaceClient, ComponentCreationStatus $status, ReplicationController $object)
    {
        $podRepository = $namespaceClient->getPodRepository();
        $pods = $podRepository->findByReplicationController($object);

        foreach ($pods as $pod) {
            $podRepository->delete($pod);
            $status->addDeleted($pod);
        }
    }

    /**
     * @param KubernetesObject $object
     *
     * @return string
     */
    private function getObjectTypeAndName(KubernetesObject $object)
    {
        $objectClass = get_class($object);
        $type = substr($objectClass, strrpos($objectClass, '/'));

        return sprintf('%s "%s"', $type, $object->getMetadata()->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof Kubernetes;
    }
}
