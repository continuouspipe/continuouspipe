<?php

namespace ContinuousPipe\River\Analytics\Keen;

use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class TideReportListener
{
    /**
     * @var KeenClient
     */
    private $keenClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param KeenClient      $keenClient
     * @param LoggerInterface $logger
     * @param TideRepository  $tideRepository
     */
    public function __construct(KeenClient $keenClient, LoggerInterface $logger, TideRepository $tideRepository)
    {
        $this->keenClient = $keenClient;
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
            $this->logger->critical('No tide created event found, unable to create keen report', [
                'tideUuid' => $event->getTideUuid(),
                'eventType' => get_class($event),
            ]);

            return;
        }

        if ($tide->getStatus() == Tide::STATUS_SUCCESS) {
            $status = 'success';
        } elseif ($tide->getStatus() == Tide::STATUS_FAILURE) {
            $status = 'failure';
        } elseif ($tide->getStatus() == Tide::STATUS_CANCELLED) {
            $status = 'cancelled';
        } else {
            $status = 'unknown';
        }

        $createdAt = $tide->getCreationDate();
        $startedAt = $tide->getStartDate() ?: $createdAt;
        $finishedAt = $tide->getFinishDate() ?: $startedAt;

        $this->keenClient->addEvent('tides', [
            'uuid' => (string) $tide->getUuid(),
            'status' => $status,
            'timing' => [
                'created_at' => $createdAt->format(\DateTime::ISO8601),
                'started_at' => $startedAt->format(\DateTime::ISO8601),
                'finished_at' => $finishedAt->format(\DateTime::ISO8601),
            ],
            'duration' => [
                'total' => $finishedAt->getTimestamp() - $createdAt->getTimeStamp(),
                'queueing' => $startedAt->getTimestamp() - $createdAt->getTimestamp(),
                'execution' => $finishedAt->getTimestamp() - $startedAt->getTimestamp(),
            ],
            'flow' => [
                'uuid' => (string) $tide->getFlowUuid(),
            ],
            'team' => [
                'slug' => $tide->getTeam()->getSlug(),
            ],
            'user' => [
                'username' => $tide->getUser()->getUsername(),
                'email' => $tide->getUser()->getEmail(),
            ],
            'code_reference' => [
                'sha1' => $tide->getCodeReference()->getCommitSha(),
                'branch' => $tide->getCodeReference()->getBranch(),
            ],
            'repository' => [
                'identifier' => $tide->getCodeReference()->getRepository()->getIdentifier(),
                'address' => $tide->getCodeReference()->getRepository()->getAddress(),
            ],
            'configuration' => $tide->getConfiguration(),
        ]);
    }
}
