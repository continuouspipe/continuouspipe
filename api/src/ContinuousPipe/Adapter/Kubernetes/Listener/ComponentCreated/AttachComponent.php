<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\ComponentCreated;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentException;
use ContinuousPipe\Adapter\Kubernetes\Event\ComponentCreated;
use ContinuousPipe\Model\Component;
use Kubernetes\Client\Exception\Exception;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use LogStream\LoggerFactory;
use LogStream\Node\Raw;
use LogStream\Node\Text;

class AttachComponent
{
    /**
     * @var DeploymentClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param LoggerFactory           $loggerFactory
     */
    public function __construct(DeploymentClientFactory $clientFactory, LoggerFactory $loggerFactory)
    {
        $this->clientFactory = $clientFactory;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param ComponentCreated $event
     */
    public function notify(ComponentCreated $event)
    {
        if (!$this->haveToAttach($event->getComponent())) {
            return;
        }

        $createdObjects = $event->getStatus()->getCreated();
        /** @var Pod[] $createdPods */
        $createdPods = array_filter($createdObjects, function (KubernetesObject $object) {
            return $object instanceof Pod;
        });

        if (0 == count($createdPods)) {
            return;
        }

        $context = $event->getContext();
        $namespaceClient = $this->clientFactory->get($context);
        $podRepository = $namespaceClient->getPodRepository();
        $logger = $this->loggerFactory->from($context->getLog());

        foreach ($createdPods as $pod) {
            $log = $logger->append(new Text(sprintf(
                'Waiting component "%s"',
                $pod->getMetadata()->getName()
            )));

            $podLogger = $this->loggerFactory->from($log);
            $podLogger->start();

            $rawLog = $podLogger->append(new Raw());
            $rawLogger = $this->loggerFactory->from($rawLog);

            try {
                $pod = $podRepository->attach($pod, function ($output) use ($rawLogger) {
                    $rawLogger->append(new Text($output));
                });

                if ($pod->getStatus()->getPhase() == PodStatus::PHASE_SUCCEEDED) {
                    $rawLogger->success();
                } else {
                    $rawLogger->failure();

                    throw new ComponentException('Did not end successfully');
                }
            } catch (Exception $e) {
                $podLogger->append(new Text($e->getMessage()));
                $podLogger->failure();
            } finally {
                $podRepository->delete($pod);
            }
        }
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
}
