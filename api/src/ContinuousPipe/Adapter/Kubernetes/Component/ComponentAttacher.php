<?php

namespace ContinuousPipe\Adapter\Kubernetes\Component;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Promise\PromiseBuilder;
use JMS\Serializer\SerializerInterface;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Complex;
use LogStream\Node\Raw;
use LogStream\Node\Text;
use Kubernetes\Client\Exception\Exception;
use React;

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
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var int
     */
    private $endpointTimeout;
    /**
     * @var int
     */
    private $endpointInterval;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param LoggerFactory $loggerFactory
     * @param SerializerInterface $serializer
     * @param int $endpointInterval
     */
    public function __construct(
        DeploymentClientFactory $clientFactory,
        LoggerFactory $loggerFactory,
        SerializerInterface $serializer,
        int $endpointTimeout,
        int $endpointInterval
    ) {
        $this->clientFactory = $clientFactory;
        $this->loggerFactory = $loggerFactory;
        $this->serializer = $serializer;
        $this->endpointTimeout = $endpointTimeout;
        $this->endpointInterval = $endpointInterval;
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
            $podName = $pod->getMetadata()->getName();

            $podLogger = $logger->child(new Text(sprintf('Waiting component "%s"', $podName)));
            $podLogger->updateStatus(Log::RUNNING);

            $rawLogger = $podLogger->child(new Raw());

            $loop = React\EventLoop\Factory::create();

            $podIsRunningPromise = (new PromiseBuilder($loop))
                ->retry(
                    $this->endpointInterval,
                    function(React\Promise\Deferred $deferred) use ($podRepository, $podName) {
                        $pod = $podRepository->findOneByName($podName);

                        if ($pod->getStatus() !== PodStatus::PHASE_PENDING) {
                            $deferred->resolve();
                        }
                    }
                )
                ->withTimeout($this->endpointTimeout)
                ->getPromise();

            $eventsLogger = $podLogger->child(new Complex('events'));
            $updateEvents = function () use ($namespaceClient, $pod, $eventsLogger) {
                $eventList = $namespaceClient->getEventRepository()->findByObject($pod);

                $events = $eventList->getEvents();
                $eventsLogger->update(
                    new Complex(
                        'events', [
                            'events' => json_decode($this->serializer->serialize($events, 'json'), true),
                        ]
                    )
                );
            };

            $timer = $loop->addPeriodicTimer($this->endpointInterval, $updateEvents);

            $podIsRunningPromise->then(
                function () use ($timer, $updateEvents) {
                    $timer->cancel();
                    $updateEvents();
                },
                function (\Throwable $reason) use ($timer, $updateEvents) {
                    $timer->cancel();
                    $updateEvents();

                    throw $reason;
                }
            );

            $loop->run();

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
