<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Pipe\Command\WaitComponentsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsReady;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Pipe\Promise\PromiseBuilder;
use ContinuousPipe\Pipe\View\ComponentStatus;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Exception\PodNotFound;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;
use React;

class WaitComponentsHandler implements DeploymentHandler
{
    /**
     * The internal to check to components.
     */
    const DEFAULT_COMPONENT_CHECK_INTERVAL = 2.5;

    /**
     * Half an hour timeout for a component to be ready.
     */
    const DEFAULT_COMPONENT_TIMEOUT = 1800;

    /**
     * @var DeploymentClientFactory
     */
    private $clientFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var float
     */
    private $checkInternal;

    /**
     * @var int
     */
    private $timeout;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param MessageBus              $eventBus
     * @param LoggerFactory           $loggerFactory
     * @param float                   $checkInternal
     * @param int                     $timeout
     */
    public function __construct(DeploymentClientFactory $clientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, $checkInternal = self::DEFAULT_COMPONENT_CHECK_INTERVAL, $timeout = self::DEFAULT_COMPONENT_TIMEOUT)
    {
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
        $this->checkInternal = $checkInternal;
        $this->timeout = $timeout;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param WaitComponentsCommand $command
     */
    public function handle(WaitComponentsCommand $command)
    {
        $objects = $this->getKubernetesObjectsToWait($command->getComponentStatuses());
        $client = $this->clientFactory->get($command->getContext());
        $logger = $this->loggerFactory->from($command->getContext()->getLog());

        $loop = React\EventLoop\Factory::create();
        $promises = [];

        foreach ($objects as $object) {
            if ($object instanceof ReplicationController) {
                $promises[] = $this->waitOneReplicationControllerPodRunning($loop, $client, $logger, $object);
            } elseif ($object instanceof Pod) {
                $promises[] = $this->waitPodRunning($loop, $client, $logger, $object);
            }
        }

        $deploymentContext = $command->getContext();
        React\Promise\all($promises)->then(function () use ($deploymentContext) {
            $deploymentUuid = $deploymentContext->getDeployment()->getUuid();

            $this->eventBus->handle(new ComponentsReady($deploymentUuid));
        })->otherwise(function () use ($deploymentContext) {
            $this->eventBus->handle(new DeploymentFailed($deploymentContext));
        });

        $loop->run();
    }

    /**
     * @param Pod $pod
     *
     * @return React\Promise\Promise
     */
    private function waitPodRunning(React\EventLoop\LoopInterface $loop, NamespaceClient $client, Logger $logger, Pod $pod)
    {
        $podName = $pod->getMetadata()->getName();

        $log = $logger->child(new Text(sprintf('Waiting pod "%s" to be running', $podName)));
        $logger = $this->loggerFactory->from($log);

        return (new PromiseBuilder($loop))
            ->retry($this->checkInternal, function (React\Promise\Deferred $deferred) use ($client, $podName) {
                try {
                    $pod = $client->getPodRepository()->findOneByName($podName);
                } catch (PodNotFound $e) {
                    return $deferred->resolve();
                }

                if ($pod->getStatus()->getPhase() == PodStatus::PHASE_RUNNING) {
                    $deferred->resolve($pod);
                }
            })
            ->withTimeout($this->timeout)
            ->getPromise()
            ->then(function () use ($logger) {
                $logger->updateStatus(Log::SUCCESS);
            }, function (\Exception $e) use ($logger) {
                $logger->updateStatus(Log::FAILURE);
                $logger->child(new Text($e->getMessage()));

                throw $e;
            })
        ;
    }

    /**
     * @param NamespaceClient       $client
     * @param ReplicationController $replicationController
     *
     * @return React\Promise\Promise
     */
    private function waitOneReplicationControllerPodRunning(React\EventLoop\LoopInterface $loop, NamespaceClient $client, Logger $logger, ReplicationController $replicationController)
    {
        $logger = $logger->child(new Text(sprintf('Waiting at least one pod of RC "%s" to be running', $replicationController->getMetadata()->getName())));

        return (new PromiseBuilder($loop))
            ->retry($this->checkInternal, function (React\Promise\Deferred $deferred) use ($client, $replicationController) {
                $pods = $client->getPodRepository()->findByReplicationController($replicationController);

                $runningPods = array_filter($pods->getPods(), function (Pod $pod) {
                    return $pod->getStatus()->getPhase() == PodStatus::PHASE_RUNNING;
                });

                if (count($runningPods) > 0) {
                    $deferred->resolve($pods);
                }
            })
            ->withTimeout($this->timeout)
            ->getPromise()
            ->then(function () use ($logger) {
                $logger->updateStatus(Log::SUCCESS);
            }, function (\Exception $e) use ($logger, $replicationController) {
                $logger->updateStatus(Log::FAILURE);
                $logger->child(new Text($e->getMessage()));

                throw $e;
            })
        ;
    }

    /**
     * @param ComponentStatus[] $componentStatuses
     *
     * @return KubernetesObject[]
     */
    private function getKubernetesObjectsToWait(array $componentStatuses)
    {
        $objects = [];

        foreach ($componentStatuses as $componentStatus) {
            if (!$componentStatus instanceof ComponentCreationStatus) {
                throw new \RuntimeException(sprintf(
                    'Expected a status of type `%s`, got %s',
                    ComponentCreationStatus::class,
                    get_class($componentStatus)
                ));
            }

            $objects = array_merge($objects, $componentStatus->getCreated(), $componentStatus->getUpdated());
        }

        return $objects;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof Kubernetes;
    }
}
