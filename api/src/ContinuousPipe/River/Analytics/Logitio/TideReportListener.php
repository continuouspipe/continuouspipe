<?php

namespace ContinuousPipe\River\Analytics\Logitio;

use ContinuousPipe\River\Analytics\Logitio\Client\LogitioClient;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Repository\EventBusTideRepository;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\View\Tide as TideView;
use ContinuousPipe\River\View\TideRepository;
use Psr\Log\LoggerInterface;

class TideReportListener
{
    /**
     * @var LogitioClient
     */
    private $logitioClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var EventBusTideRepository
     */
    private $eventBusTideRepository;

    public function __construct(
        LogitioClient $logitioClient,
        LoggerInterface $logger,
        TideRepository $tideRepository,
        EventBusTideRepository $eventBusTideRepository
    ) {
        $this->logitioClient = $logitioClient;
        $this->logger = $logger;
        $this->tideRepository = $tideRepository;
        $this->eventBusTideRepository = $eventBusTideRepository;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        try {
            $tide = $this->eventBusTideRepository->find($event->getTideUuid());
            $tideView = $this->tideRepository->find($event->getTideUuid());
        } catch (TideNotFound $e) {
            $this->logTideNotFound($event);

            return;
        }

        $this->logTideEvent($tide, $tideView);

        foreach ($tide->getTasks()->getTasks() as $task) {
            $this->logTaskEvent($tide, $task);
        }
    }

    private function getTideStatusCode(Tide $tide): int
    {
        $statusCodes = [
            Tide::STATUS_SUCCESS => 200,
            Tide::STATUS_FAILURE => 500,
            Tide::STATUS_CANCELLED => 400,
        ];

        if (!isset($statusCodes[$tide->getStatus()])) {
            return 501;
        }

        return $statusCodes[$tide->getStatus()];
    }

    private function getTaskStatusCode(Task $task): int
    {
        $statusCodes = [
            Task::STATUS_SUCCESSFUL => 200,
            Task::STATUS_SKIPPED => 201,
            Task::STATUS_PENDING => 300,
            Task::STATUS_RUNNING => 301,
            Task::STATUS_CANCELLED => 302,
            Task::STATUS_FAILED=> 500,
        ];

        if (!isset($statusCodes[$task->getStatus()])) {
            return 501;
        }

        return $statusCodes[$task->getStatus()];
    }

    private function getStatusReason(Tide $tide): string
    {
        if (null !== $reason = $tide->getFailureReason()) {
            return $reason;
        }

        return $tide->getStatus();
    }

    private function logTideEvent(Tide $tide, TideView $tideView)
    {
        $createdAt = $tideView->getCreationDate();
        $finishedAt = $tideView->getFinishDate() ?: $tideView->getStartDate() ?: $createdAt;

        $this->logitioClient->addEvent(
            'tides',
            [
                'flow_uuid' => (string) $tide->getFlowUuid(),
                'username' => $tide->getUser()->getUsername(),
                'project' => $tide->getTeam()->getName(),
                'flow_name' => $tide->getCodeReference()->getRepository()->getAddress(),
                'tide_uuid' => (string) $tide->getUuid(),
                'duration' => $finishedAt->getTimestamp() - $createdAt->getTimestamp(),
                'number_of_tasks' => $tide->getTasks()->count(),
                'branch_name' => $tide->getCodeReference()->getBranch(),
                'commit_sha1' => $tide->getCodeReference()->getCommitSha(),
                'status' => [
                    'code' => $this->getTideStatusCode($tide),
                    'reason' => $this->getStatusReason($tide)
                ],
            ]
        );
    }

    private function logTaskEvent(Tide $tide, Task $task)
    {
        $this->logitioClient->addEvent(
            'tides_tasks',
            [
                'username' => $tide->getUser()->getUsername(),
                'project' => $tide->getTeam()->getName(),
                'tide_uuid' => (string) $tide->getUuid(),
                'flow_uuid' => (string) $tide->getFlowUuid(),
                'task_type' => (string) substr(strrchr(get_class($task), '\\'), 1),
                'task_name' => $task->getIdentifier(),
                'status' => [
                    'code' => $this->getTaskStatusCode($task),
                    'reason' => $task->getStatus()
                ],
            ]
        );
    }

    private function logTideNotFound(TideEvent $event)
    {
        $this->logger->critical(
            'No tide created event found, unable to create logitio report',
            [
                'tideUuid' => $event->getTideUuid(),
                'eventType' => get_class($event),
            ]
        );
    }
}
