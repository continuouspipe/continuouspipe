<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentAttacher;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentException;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicServiceVoter;
use ContinuousPipe\Adapter\Kubernetes\Transformer\ComponentTransformer;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Command\CreateComponentsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use Kubernetes\Client\Exception\ClientError;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\ObjectRepository;
use Kubernetes\Client\Repository\WrappedObjectRepository;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

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
     * @var PublicServiceVoter
     */
    private $publicServiceVoter;

    /**
     * @var ComponentAttacher
     */
    private $attacher;

    /**
     * @param ComponentTransformer    $componentTransformer
     * @param DeploymentClientFactory $clientFactory
     * @param MessageBus              $eventBus
     * @param LoggerFactory           $loggerFactory
     * @param PublicServiceVoter      $publicServiceVoter
     * @param ComponentAttacher       $attacher
     */
    public function __construct(ComponentTransformer $componentTransformer, DeploymentClientFactory $clientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, PublicServiceVoter $publicServiceVoter, ComponentAttacher $attacher)
    {
        $this->componentTransformer = $componentTransformer;
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->publicServiceVoter = $publicServiceVoter;
        $this->attacher = $attacher;
    }

    /**
     * @param CreateComponentsCommand $command
     */
    public function handle(CreateComponentsCommand $command)
    {
        $context = $command->getContext();
        $client = $this->clientFactory->get($context);

        $environment = $context->getEnvironment();
        $logger = $this->loggerFactory->from($context->getLog());

        foreach ($environment->getComponents() as $component) {
            try {
                $status = $this->createComponent($client, $logger, $component);

                if ($this->haveToAttach($component)) {
                    $this->attacher->attach($context, $status);
                }
            } catch (ClientError $e) {
                $logger->append(new Text(sprintf(
                    'An error appeared while creating the component "%s": %s',
                    $component->getName(),
                    $e->getMessage()
                )));

                $this->eventBus->handle(new DeploymentFailed($context->getDeployment()->getUuid()));
            } catch (ComponentException $e) {
                $this->eventBus->handle(new DeploymentFailed($context->getDeployment()->getUuid()));
            }
        }

        $this->eventBus->handle(new ComponentsCreated($context));
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
            if ($this->publicServiceVoter->isAPublicService($object)) {
                $logger->append(new Text('Ignoring the public service '.$this->getObjectTypeAndName($object)));

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

        if ($objectRepository->exists($objectName)) {
            if ($component->isLocked()) {
                $logger->append(new Text('NOT updated '.$this->getObjectTypeAndName($object).' because it is locked'));

                return;
            }

            $logger->append(new Text('Updating '.$this->getObjectTypeAndName($object)));
            $objectRepository->update($object);
            $status->addUpdated($object);

            // Has an extremely simple RC-update feature, we can delete matching RC's pods
            // Wait the "real" rolling-update feature
            // @link https://github.com/sroze/continuouspipe/issues/54
            if ($object instanceof ReplicationController) {
                $this->deleteReplicationControllerPods($client, $status, $object);
            }
        } else {
            $logger->append(new Text('Creating '.$this->getObjectTypeAndName($object)));
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
     * Returns true if the component have to be attached.
     *
     * @param Component $component
     *
     * @return bool
     */
    private function haveToAttach(Component $component)
    {
        if ($deploymentStrategy = $component->getDeploymentStrategy()) {
            return $deploymentStrategy->isAttached();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }
}
