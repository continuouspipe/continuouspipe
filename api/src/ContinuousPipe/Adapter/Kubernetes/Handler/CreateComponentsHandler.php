<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Pipe\Command\CreateComponentsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\ObjectRepository;
use Kubernetes\Client\Repository\WrappedObjectRepository;
use LogStream\Logger;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class CreateComponentsHandler implements DeploymentHandler
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
     * @param CreateComponentsCommand $command
     */
    public function handle(CreateComponentsCommand $command)
    {
        $context = $command->getContext();
        $client = $this->clientFactory->get($context);

        $environment = $context->getEnvironment();
        $namespaceObjects = $this->environmentTransformer->getElementListFromEnvironment($environment);

        foreach ($namespaceObjects as $object) {
            $this->createComponent($client, $object, $context->getLogger());
        }

        $this->eventBus->handle(new ComponentsCreated($context));
    }

    /**
     * Create a component.
     *
     * @param NamespaceClient  $client
     * @param KubernetesObject $object
     * @param Logger           $logger
     */
    private function createComponent(NamespaceClient $client, KubernetesObject $object, Logger $logger)
    {
        $objectRepository = $this->getObjectRepository($client, $object);
        $objectName = $object->getMetadata()->getName();

        if ($objectRepository->exists($objectName)) {
            if ($this->isLocked($object)) {
                $logger->append(new Text('NOT updated '.$this->getObjectTypeAndName($object).' because it is locked'));

                return;
            }

            $logger->append(new Text('Updating '.$this->getObjectTypeAndName($object)));
            $objectRepository->update($object);

            // Has an extremely simple RC-update feature, we can delete matching RC's pods
            // Wait the "real" rolling-update feature
            // @link https://github.com/sroze/continuouspipe/issues/54
            if ($object instanceof ReplicationController) {
                $this->deleteReplicationControllerPods($client, $object);
            }
        } else {
            $logger->append(new Text('Creating '.$this->getObjectTypeAndName($object)));
            $objectRepository->create($object);
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
     * @param NamespaceClient       $namespaceClient
     * @param ReplicationController $object
     */
    private function deleteReplicationControllerPods(NamespaceClient $namespaceClient, ReplicationController $object)
    {
        $podRepository = $namespaceClient->getPodRepository();
        $pods = $podRepository->findByReplicationController($object);

        foreach ($pods as $pod) {
            $podRepository->delete($pod);
        }
    }

    /**
     * @param KubernetesObject $object
     *
     * @return bool
     */
    private function isLocked(KubernetesObject $object)
    {
        $labelList = $object->getMetadata()->getLabelList();
        $locked = $labelList->hasKey('com.continuouspipe.locked');

        return $locked;
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
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }
}
