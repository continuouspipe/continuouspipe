<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Pipe\Command\WaitComponentsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsReady;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Pipe\View\ComponentStatus;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\NamespaceClient;
use SimpleBus\Message\Bus\MessageBus;
use React;

class WaitComponentsHandler implements DeploymentHandler
{
    /**
     * @var DeploymentClientFactory
     */
    private $clientFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param MessageBus              $eventBus
     */
    public function __construct(DeploymentClientFactory $clientFactory, MessageBus $eventBus)
    {
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
    }

    /**
     * @param WaitComponentsCommand $command
     */
    public function handle(WaitComponentsCommand $command)
    {
        $objects = $this->getKubernetesObjectsToWait($command->getComponentStatuses());
        $client = $this->clientFactory->get($command->getContext());

        $loop = React\EventLoop\Factory::create();
        $promises = [];

        foreach ($objects as $object) {
            if ($object instanceof ReplicationController) {
                $promises[] = $this->waitOneReplicationControllerPodRunning($loop, $client, $object);
            } elseif ($object instanceof Pod) {
                $promises[] = $this->waitPodRunning($loop, $client, $object);
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
    private function waitPodRunning(React\EventLoop\LoopInterface $loop, NamespaceClient $client, Pod $pod)
    {
        $deferred = new React\Promise\Deferred();

        // Each, 1 second, get the pod status
        $loop->addPeriodicTimer(1.0, function (React\EventLoop\Timer\Timer $timer) use (&$i, $deferred, $client, $pod) {
            $pod = $client->getPodRepository()->findOneByName($pod->getMetadata()->getName());
            $deferred->notify($pod);

            if ($pod->getStatus()->getPhase() == PodStatus::PHASE_RUNNING) {
                $timer->cancel();
                $deferred->resolve($pod);
            } elseif (++$i >= 66) {
                $timer->cancel();
                $deferred->reject($pod);
            }
        });

        return $deferred->promise();
    }

    /**
     * @param NamespaceClient       $client
     * @param ReplicationController $replicationController
     *
     * @return React\Promise\Promise
     */
    private function waitOneReplicationControllerPodRunning(React\EventLoop\LoopInterface $loop, NamespaceClient $client, ReplicationController $replicationController)
    {
        $deferred = new React\Promise\Deferred();

        // Each, 1 second, get the pod status
        $loop->addPeriodicTimer(1.0, function (React\EventLoop\Timer\Timer $timer) use (&$i, $deferred, $client, $replicationController) {
            $pods = $client->getPodRepository()->findByReplicationController($replicationController);
            $deferred->notify($pods);

            $runningPods = array_filter($pods->getPods(), function (Pod $pod) {
                return $pod->getStatus()->getPhase() == PodStatus::PHASE_RUNNING;
            });

            if (count($runningPods) > 0) {
                $deferred->notify($pods);
                $timer->cancel();
            } elseif (++$i >= 10) {
                $timer->cancel();
                $deferred->reject($pods);
            }
        });

        return $deferred->promise();
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
