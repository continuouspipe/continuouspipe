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
            $this->logger->critical(
                'No tide created event found, unable to create logitio report',
                [
                    'tideUuid' => $event->getTideUuid(),
                    'eventType' => get_class($event),
                ]
            );

            return;
        }

        $statusCode = $this->getStatusCode($tide);
        $this->logTideEvent($tide, $statusCode, $tideView);

        foreach($tide->getTasks()->getTasks() as $task) {
            $this->logTaskEvent($tide, $task, $statusCode);
        }

    }

    private function getStatusCode(Tide $tide): int
    {
        $status = $tide->getStatus();
        if ($status == Tide::STATUS_SUCCESS) {
            return 200;
        }

        if ($status == Tide::STATUS_FAILURE) {
            return 500;
        }

        if ($status == Tide::STATUS_CANCELLED) {
            return 400;
        }

        return 501;
    }

    private function getStatusReason(Tide $tide): string
    {
        if (null !== $reason = $tide->getFailureReason()) {
            return $reason;
        }

        return $tide->getStatus();
    }

    private function logTideEvent(Tide $tide, int $statusCode, TideView $tideView)
    {
        $createdAt = $tideView->getCreationDate();
        $startedAt = $tideView->getStartDate() ?: $createdAt;
        $finishedAt = $tideView->getFinishDate() ?: $startedAt;

        $this->logitioClient->addEvent(
            'tides',
            [
                'flow_uuid' => (string) $tide->getFlowUuid(),
                'username' => $tide->getUser()->getUsername(),
                'project' => $tide->getTeam()->getName(),
                'flow_name' => $tide->getCodeReference()->getRepository()->getAddress(),
                'timestamp' => time(),
                'tide_uuid' => (string) $tide->getUuid(),
                'duration' => $finishedAt->getTimestamp() - $createdAt->getTimestamp(),
                'number_of_tasks' => $tide->getTasks()->count(),
                'branch_name' => $tide->getCodeReference()->getBranch(),
                'commit_sha1' => $tide->getCodeReference()->getCommitSha(),
                'status' => [
                    'code' => $statusCode,
                    'reason' => $this->getStatusReason($tide)
                ],
            ]
        );
    }

    private function logTaskEvent(Tide $tide, Task $task, int $statusCode)
    {
        $this->logitioClient->addEvent(
            'tides_tasks',
            [
                'tide_uuid' => (string) $tide->getUuid(),
                'flow_uuid' => (string) $tide->getFlowUuid(),
                'task_type' => (string) substr(strrchr(get_class($task), '\\'), 1),
                //'task_name' => ???,
                'status' => [
                    'code' => $statusCode,
                    'reason' => $task->getStatus()
                ],
            ]
        );
    }
}
