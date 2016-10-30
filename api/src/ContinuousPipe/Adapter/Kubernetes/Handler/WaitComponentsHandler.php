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
use Kubernetes\Client\Model\Container;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use Kubernetes\Client\Model\PodStatusCondition;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Complex;
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
            } elseif ($object instanceof Deployment) {
                $promises[] = $this->waitDeploymentFinished($loop, $client, $logger, $object);
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
     * @param NamespaceClient       $client
     * @param ReplicationController $replicationController
     *
     * @return React\Promise\Promise
     */
    private function waitOneReplicationControllerPodRunning(React\EventLoop\LoopInterface $loop, NamespaceClient $client, Logger $logger, ReplicationController $replicationController)
    {
        $logger = $logger->child(new Text(sprintf('Waiting at least one pod of RC "%s" to be running', $replicationController->getMetadata()->getName())));
        $logger->updateStatus(Log::RUNNING);

        return (new PromiseBuilder($loop))
            ->retry($this->checkInternal, function (React\Promise\Deferred $deferred) use ($client, $replicationController) {
                $pods = $client->getPodRepository()->findByReplicationController($replicationController);

                $runningPods = array_filter($pods->getPods(), function (Pod $pod) {
                    if (null === ($status = $pod->getStatus())) {
                        return false;
                    }

                    return $this->isPodRunningAndReady($status);
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
     * @param NamespaceClient $client
     * @param Deployment      $deployment
     *
     * @return React\Promise\Promise
     */
    private function waitDeploymentFinished(React\EventLoop\LoopInterface $loop, NamespaceClient $client, Logger $logger, Deployment $deployment)
    {
        $logger = $logger->child(new Text(sprintf('Rolling update of component "%s"', $deployment->getMetadata()->getName())));
        $logger->updateStatus(Log::RUNNING);

        // Display status of the deployment
        $statusLogger = $logger->child(new Text('Deployment is starting'));
        $deploymentStatusPromise = (new PromiseBuilder($loop))
            ->retry($this->checkInternal, function (React\Promise\Deferred $deferred) use ($client, $statusLogger, $deployment, $statusLogger) {
                $foundDeployment = $client->getDeploymentRepository()->findOneByName($deployment->getMetadata()->getName());
                $status = $foundDeployment->getStatus();

                if (null === $status) {
                    $statusLogger->update(new Text('Deployment is not started yet'));

                    return;
                }

                $statusLogger->update(new Text(sprintf(
                    '%d/%d available replicas - %d updated',
                    $status->getAvailableReplicas(),
                    $status->getReplicas(),
                    $status->getUpdatedReplicas()
                )));

                if ($status->getAvailableReplicas() == $status->getReplicas()) {
                    $deferred->resolve($deployment);
                }
            })
            ->withTimeout($this->timeout)
            ->getPromise()
        ;

        // Display the status of the pods related to this deployment
        $podsLogger = $logger->child(new Complex('pods'));
        $updatePodStatuses = function () use ($client, $deployment, $podsLogger) {
            $podsFoundByLabel = $client->getPodRepository()->findByLabels($deployment->getSpecification()->getSelector()->getMatchLabels());

            $podsLogger->update(new Complex('pods', [
                'deployment' => $this->normalizeDeployment($deployment),
                'pods' => array_map(function (Pod $pod) {
                    return $this->normalizePod($pod);
                }, $podsFoundByLabel->getPods()),
            ]));
        };

        $updatePodStatusesTimer = $loop->addPeriodicTimer(1, $updatePodStatuses);

        return $deploymentStatusPromise
            ->then(function () use ($logger, $updatePodStatusesTimer, $updatePodStatuses) {
                $updatePodStatusesTimer->cancel();
                $updatePodStatuses();

                $logger->updateStatus(Log::SUCCESS);
            }, function (\Exception $e) use ($logger, $statusLogger, $updatePodStatusesTimer, $updatePodStatuses) {
                $updatePodStatusesTimer->cancel();
                $updatePodStatuses();

                $logger->updateStatus(Log::FAILURE);
                $statusLogger->update(new Text($e->getMessage()));

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

    /**
     * @param PodStatus $status
     *
     * @return bool
     */
    protected function isPodRunningAndReady(PodStatus $status)
    {
        if ($status->getPhase() != PodStatus::PHASE_RUNNING) {
            return false;
        }

        /** @var PodStatusCondition $readyCondition */
        $readyCondition = current(array_filter($status->getConditions(), function (PodStatusCondition $condition) {
            return $condition->getType() == 'Ready';
        }));

        if ($readyCondition === false) {
            // No Ready condition found so... let's skip.
            return true;
        }

        return $readyCondition->isStatus();
    }

    /**
     * @param Deployment $deployment
     *
     * @return array
     */
    private function normalizeDeployment(Deployment $deployment)
    {
        $specification = $deployment->getSpecification();
        $containers = $specification->getTemplate()->getPodSpecification()->getContainers();

        return [
            'replicas' => $specification->getReplicas(),
            'containers' => array_map(function (Container $container) {
                return $this->normalizeContainer($container);
            }, $containers),
        ];
    }

    /**
     * @param Pod $pod
     *
     * @return array
     */
    private function normalizePod(Pod $pod)
    {
        return [
            'name' => $pod->getMetadata()->getName(),
            'creationTimestamp' => $pod->getMetadata()->getCreationTimestamp(),
            'deletionTimestamp' => $pod->getMetadata()->getDeletionTimestamp(),
            'containers' => array_map(function (Container $container) {
                return $this->normalizeContainer($container);
            }, $pod->getSpecification()->getContainers()),
            'status' => null !== $pod->getStatus() ? $this->normalizePodStatus($pod->getStatus()) : null,
        ];
    }

    /**
     * @param Container $container
     *
     * @return array
     */
    private function normalizeContainer(Container $container)
    {
        return [
            'name' => $container->getName(),
            'image' => $container->getImage(),
        ];
    }

    /**
     * @param PodStatus $status
     *
     * @return array
     */
    private function normalizePodStatus(PodStatus $status)
    {
        return [
            'phase' => $status->getPhase(),
            'ready' => $this->isPodRunningAndReady($status),
        ];
    }
}
