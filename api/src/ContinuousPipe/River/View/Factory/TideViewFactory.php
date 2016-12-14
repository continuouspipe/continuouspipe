<?php

namespace ContinuousPipe\River\View\Factory;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideTaskView;
use ContinuousPipe\River\View\TimeResolver;
use Ramsey\Uuid\UuidInterface;

class TideViewFactory
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var TimeResolver
     */
    private $timeResolver;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param TideRepository $tideRepository
     * @param TimeResolver   $timeResolver
     * @param EventStore     $eventStore
     */
    public function __construct(TideRepository $tideRepository, TimeResolver $timeResolver, EventStore $eventStore)
    {
        $this->tideRepository = $tideRepository;
        $this->timeResolver = $timeResolver;
        $this->eventStore = $eventStore;
    }

    /**
     * @param UuidInterface $tideUuid
     *
     * @return Tide
     */
    public function create(UuidInterface $tideUuid) : Tide
    {
        $tide = $this->tideRepository->find($tideUuid);

        $createdAt = $this->getFirstEventDateTime($tideUuid, TideCreated::class) ?: new \DateTime();
        $startedAt = $this->getFirstEventDateTime($tideUuid, TideStarted::class) ?: new \DateTime();
        $failedAt = $this->getFirstEventDateTime($tideUuid, TideFailed::class);
        $succeedAt = $this->getFirstEventDateTime($tideUuid, TideSuccessful::class);
        $finishedAt = $succeedAt ?: $failedAt ?: new \DateTime();

        $view = Tide::create(
            $tideUuid,
            $tide->getFlowUuid(),
            $tide->getCodeReference(),
            $tide->getLog(),
            $tide->getTeam(),
            $tide->getUser(),
            $tide->getConfiguration(),
            $createdAt
        );

        $view->setStartDate($startedAt);
        $view->setFinishDate($finishedAt);
        $view->setStatus(Tide::STATUS_PENDING);
        $view->setTasks(array_map(function (Task $task) {
            return TideTaskView::fromTask($task);
        }, $tide->getTasks()->getTasks()));

        if ($tide->isRunning()) {
            $view->setStatus(Tide::STATUS_RUNNING);
        } elseif ($tide->isFailed()) {
            $view->setStatus(Tide::STATUS_FAILURE);
        } elseif ($tide->isSuccessful()) {
            $view->setStatus(Tide::STATUS_SUCCESS);
        }

        return $view;
    }

    /**
     * @param UuidInterface $tideUuid
     * @param string        $className
     *
     * @return \DateTime|null
     */
    private function getFirstEventDateTime(UuidInterface $tideUuid, $className)
    {
        $events = $this->eventStore->findByTideUuidAndTypeWithMetadata($tideUuid, $className);
        if (count($events) == 0) {
            return;
        }

        return $events[0]->getDateTime();
    }
}
