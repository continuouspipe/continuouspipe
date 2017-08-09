<?php

namespace ContinuousPipe\Adapter\Kubernetes\Component;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Promise\PromiseBuilder;
use JMS\Serializer\SerializerInterface;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodStatus;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\PodRepository;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Complex;
use LogStream\Node\Raw;
use LogStream\Node\Text;
use Kubernetes\Client\Exception\Exception;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $applicationLog;
    /**
     * @var int
     */
    private $podTimeout;
    /**
     * @var int
     */
    private $podInterval;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param LoggerFactory $loggerFactory
     * @param SerializerInterface $serializer
     * @param LoggerInterface $applicationLog
     * @param int $podTimeout
     * @param int $podInterval
     */
    public function __construct(
        DeploymentClientFactory $clientFactory,
        LoggerFactory $loggerFactory,
        SerializerInterface $serializer,
        LoggerInterface $applicationLog,
        int $podTimeout,
        int $podInterval
    ) {
        $this->clientFactory = $clientFactory;
        $this->loggerFactory = $loggerFactory;
        $this->serializer = $serializer;
        $this->applicationLog = $applicationLog;
        $this->podTimeout = $podTimeout;
        $this->podInterval = $podInterval;
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

            // Create the tabs logging
            $tabsLogger = $podLogger->child(new Complex('tabs', [
                'tabs' => [
                    '1_logs' => [
                        'name' => 'Logs',
                        'contents' => [
                            'type' => 'raw',
                        ],
                    ],
                    '2_events' => [
                        'name' => 'Events',
                        'contents' => [
                            'type' => 'events',
                            'events' => [],
                        ],
                    ],
                ],
            ]));

            $eventsLogger = $this->loggerFactory->fromId(
                $tabsLogger->getLog()->getId().'/tabs/events/contents'
            );

            $this->logPodEventsWhilstItIsNotRunning($pod, $podRepository, $namespaceClient, $eventsLogger);

            try {
                $rawLogger = $this->loggerFactory->fromId(
                    $tabsLogger->getLog()->getId().'/tabs/logs/contents'
                );

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

    private function logPodEventsWhilstItIsNotRunning(
        Pod $pod,
        PodRepository $podRepository,
        NamespaceClient $namespaceClient,
        Logger $eventsLogger
    ) {
        $podName = $pod->getMetadata()->getName();

        $loop = React\EventLoop\Factory::create();

        $podIsRunningPromise = (new PromiseBuilder($loop))
            ->retry(
                $this->podInterval,
                function (React\Promise\Deferred $deferred) use ($podRepository, $podName) {
                    $pod = $podRepository->findOneByName($podName);

                    if ($pod->getStatus() !== PodStatus::PHASE_PENDING) {
                        $deferred->resolve();
                    }
                }
            )
            ->withTimeout($this->podTimeout)
            ->getPromise();

        $updateEvents = function () use ($namespaceClient, $pod, $eventsLogger) {
            $eventList = $namespaceClient->getEventRepository()->findByObject($pod);
            $events = $eventList->getEvents();

            $eventsLogger->update(new Complex('events', [
                'events' => json_decode($this->serializer->serialize($events, 'json'), true),
            ]));
        };

        $timer = $loop->addPeriodicTimer($this->podInterval, $updateEvents);

        $podIsRunningPromise->then(
            function () use ($timer, $updateEvents) {
                $timer->cancel();
                $updateEvents();
            },
            function (\Throwable $reason) use ($timer, $updateEvents) {
                $timer->cancel();
                $updateEvents();

                $this->applicationLog->warning(
                    'Something went wrong while waiting the pod to be running',
                    [
                        'exception' => $reason,
                    ]
                );
            }
        );

        $loop->run();
    }
}
