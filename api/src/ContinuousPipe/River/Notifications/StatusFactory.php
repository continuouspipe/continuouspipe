<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TimeResolver;
use Psr\Log\LoggerInterface;

class StatusFactory
{
    /**
     * @var TimeResolver
     */
    private $timeResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var string
     */
    private $uiBaseUrl;

    /**
     * @param TimeResolver    $timeResolver
     * @param LoggerInterface $logger
     * @param EventStore      $eventStore
     * @param string          $uiBaseUrl
     */
    public function __construct(TimeResolver $timeResolver, LoggerInterface $logger, EventStore $eventStore, $uiBaseUrl)
    {
        $this->timeResolver = $timeResolver;
        $this->logger = $logger;
        $this->eventStore = $eventStore;
        $this->uiBaseUrl = $uiBaseUrl;
    }

    /**
     * @param Tide      $tide
     * @param TideEvent $event
     *
     * @return Status
     */
    public function createFromTideAndEvent(Tide $tide, TideEvent $event)
    {
        $description = $this->generateDescription($tide, $event);
        $url = $this->generateUrl($tide);

        if ($event instanceof TideCreated) {
            $status = Status::STATE_PENDING;
        } elseif ($event instanceof TideSuccessful) {
            $status = Status::STATE_SUCCESS;
        } elseif ($event instanceof TideFailed) {
            $status = Status::STATE_FAILURE;
        } elseif ($event instanceof TideStarted) {
            $status = Status::STATE_RUNNING;
        } else {
            $status = Status::STATE_UNKNOWN;
        }

        return new Status($status, $description, $url, $this->getPublicEndpoints($tide, $event));
    }

    /**
     * @param Tide      $tide
     * @param TideEvent $event
     *
     * @return PublicEndpoint[]
     */
    private function getPublicEndpoints(Tide $tide, TideEvent $event)
    {
        $tideEvents = $this->eventStore->findByTideUuid($tide->getUuid());
        $tideEvents[] = $event;

        $deploymentSuccessfulEvents = array_values(array_filter($tideEvents, function (TideEvent $event) {
            return $event instanceof DeploymentSuccessful;
        }));

        $endpoints = array_reduce($deploymentSuccessfulEvents, function ($endpoints, DeploymentSuccessful $deploymentSuccessful) {
            return array_merge($endpoints, $deploymentSuccessful->getDeployment()->getPublicEndpoints());
        }, []);

        return $endpoints;
    }

    /**
     * @param Tide      $tide
     * @param TideEvent $event
     *
     * @return string
     */
    private function generateDescription(Tide $tide, TideEvent $event)
    {
        if ($event instanceof TideCreated) {
            return 'Pending';
        } elseif ($event instanceof TideStarted) {
            return 'Running';
        } elseif ($event instanceof TideSuccessful) {
            return sprintf('Successfully ran in %s', $this->getDurationString($tide));
        } elseif ($event instanceof TideFailed) {
            return $event->getReason();
        }

        $this->logger->warning('Generated an unknown notification description', [
            'tide' => (string) $tide->getUuid(),
        ]);

        return 'Unknown';
    }

    /**
     * @param Tide $tide
     *
     * @return string
     */
    private function getDurationString(Tide $tide)
    {
        if ($tide->getStartDate() !== null) {
            $duration = $this->timeResolver->resolve()->getTimestamp() - $tide->getStartDate()->getTimestamp();

            return gmdate('i\m s\s', $duration);
        }

        return '0';
    }

    /**
     * @param Tide $tide
     *
     * @return string
     */
    private function generateUrl(Tide $tide)
    {
        return sprintf(
            '%s/team/%s/%s/%s/logs',
            $this->getUiBaseUrl(),
            $tide->getTeam()->getSlug(),
            (string) $tide->getFlowUuid(),
            (string) $tide->getUuid()
        );
    }

    /**
     * @return string
     */
    private function getUiBaseUrl()
    {
        $baseUrl = $this->uiBaseUrl;

        if (strpos($baseUrl, 'http') !== 0) {
            $baseUrl = 'https://'.$baseUrl;
        }

        return $baseUrl;
    }
}
