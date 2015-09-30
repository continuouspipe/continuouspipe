<?php

namespace ContinuousPipe\Adapter\Kubernetes\Component;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use LogStream\LoggerFactory;
use LogStream\Node\Raw;
use LogStream\Node\Text;
use Kubernetes\Client\Exception\Exception;

class ComponentAttacher
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
     * @param DeploymentContext $context
     * @param ComponentCreationStatus $status
     * @throws ComponentException
     */
    public function attach(DeploymentContext $context, ComponentCreationStatus $status)
    {
        $createdObjects = $status->getCreated();

        /** @var Pod[] $createdPods */
        $createdPods = array_filter($createdObjects, function (KubernetesObject $object) {
            return $object instanceof Pod;
        });

        if (0 == count($createdPods)) {
            return;
        }

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

            $podLogger->success();
        }
    }
}
