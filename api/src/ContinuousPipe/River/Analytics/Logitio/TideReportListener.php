<?php

namespace ContinuousPipe\River\Analytics\Logitio;

use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;
use ContinuousPipe\River\Analytics\Logitio\Client\LogitioClient;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
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

    public function __construct(LogitioClient $logitioClient, LoggerInterface $logger, TideRepository $tideRepository)
    {
        $this->logitioClient = $logitioClient;
        $this->logger = $logger;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        try {
            $tide = $this->tideRepository->find($event->getTideUuid());
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

        if ($tide->getStatus() == Tide::STATUS_SUCCESS) {
            $statusCode = 200;
        } elseif ($tide->getStatus() == Tide::STATUS_FAILURE) {
            $statusCode = 500;
        } elseif ($tide->getStatus() == Tide::STATUS_CANCELLED) {
            $statusCode = 400;
        } else {
            $statusCode = 501;
        }

        $createdAt = $tide->getCreationDate();
        $startedAt = $tide->getStartDate() ?: $createdAt;
        $finishedAt = $tide->getFinishDate() ?: $startedAt;

        $this->logitioClient->addEvent(
            'TideLog',
            [
                'flow_uuid' => (string) $tide->getFlowUuid(),
                'username' => $tide->getUser()->getUsername(),
                'project' => $tide->getTeam()->getName(),
                //'flow_name' => ???
                'timestamp' => time(),
                'tide_uuid' => (string) $tide->getUuid(),
                'duration' => $finishedAt->getTimestamp() - $createdAt->getTimeStamp(),
                'number_of_tasks' => count($tide->getTasks()),
                'branch_name' => $tide->getCodeReference()->getBranch(),
                'commit_sha1' => $tide->getCodeReference()->getCommitSha(),
                //'generation_type' => ???
                'status' => [
                    'code' => $statusCode
                    //'reason' => ???
                    //'debug_identifier' => ???
                ],
            ]
        );
    }
}
