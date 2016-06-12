<?php

namespace ContinuousPipe\Adapter\Kubernetes\Component;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use LogStream\Log;
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
     * @param DeploymentContext       $context
     * @param ComponentCreationStatus $status
     *
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
            $podLogger = $logger->child(new Text(sprintf('Waiting component "%s"', $pod->getMetadata()->getName())));
            $podLogger->updateStatus(Log::RUNNING);

            $rawLogger = $podLogger->child(new Raw());

            try {
                $pod = $podRepository->attach($pod, function ($output) use ($rawLogger) {
                    $rawLogger->child(new Text($output));
                });

                if ($pod->getStatus()->getPhase() == PodStatus::PHASE_SUCCEEDED) {
                    $rawLogger->updateStatus(Log::SUCCESS);
                } else {
                    $rawLogger->updateStatus(Log::FAILURE);
                    $podLogger->updateStatus(Log::FAILURE);

                    throw new ComponentException('Did not end successfully');
                }
            } catch (Exception $e) {
                $podLogger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);
                $podLogger->updateStatus(Log::FAILURE);
            } finally {
                $podRepository->delete($pod);
            }

            $podLogger->updateStatus(Log::SUCCESS);
        }
    }
}
