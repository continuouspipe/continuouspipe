<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\Tide as TideAggregate;
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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var string
     */
    private $uiBaseUrl;

    /**
     * @param TimeResolver $timeResolver
     * @param LoggerInterface $logger
     * @param TideRepository $tideRepository
     * @param string $uiBaseUrl
     */
    public function __construct(TimeResolver $timeResolver, LoggerInterface $logger, TideRepository $tideRepository, $uiBaseUrl)
    {
        $this->timeResolver = $timeResolver;
        $this->logger = $logger;
        $this->tideRepository = $tideRepository;
        $this->uiBaseUrl = $uiBaseUrl;
    }

    /**
     * @param Tide $tideView
     *
     * @return Status
     */
    public function createFromTideAndEvent(Tide $tideView)
    {
        $tide = $this->tideRepository->find($tideView->getUuid());

        switch ($tideView->getStatus()) {
            case Tide::STATUS_RUNNING:
                $status = Status::STATE_RUNNING;
                $description = 'Running';
                break;
            case Tide::STATUS_FAILURE:
            case Tide::STATUS_CANCELLED:
                $status = Status::STATE_FAILURE;
                $description = $tide->getFailureReason();
                break;
            case Tide::STATUS_SUCCESS:
                $status = Status::STATE_SUCCESS;
                $description = sprintf('Successfully ran in %s', $this->getDurationString($tideView));
                break;
            default:
                $status = Status::STATE_PENDING;
                $description = 'Pending';
                break;
        }

        return new Status(
            $status,
            $description,
            $this->generateUrl($tideView),
            $this->getPublicEndpoints($tide)
        );
    }

    /**
     * @param TideAggregate $tide
     *
     * @return PublicEndpoint[]
     */
    private function getPublicEndpoints(TideAggregate $tide)
    {
        /** @var DeployTask[] $tasks */
        $tasks = $tide->getTasks()->ofType(DeployTask::class);
        $endpoints = [];

        foreach ($tasks as $task) {
            foreach ($task->getPublicEndpoints() as $publicEndpoint) {
                if (!in_array($publicEndpoint, $endpoints)) {
                    $endpoints[] = $publicEndpoint;
                }
            }
        }

        return $endpoints;
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
