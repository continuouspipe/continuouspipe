<?php

namespace ContinuousPipe\River\Bridge\Builder;

use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use Ramsey\Uuid\Uuid;
use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\View\BuildViewRepository;

class ListenForBuildStatus
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var BuildViewRepository
     */
    private $buildRepository;

    /**
     * @param TransactionManager $transactionManager
     * @param BuildViewRepository $buildRepository
     */
    public function __construct(TransactionManager $transactionManager, BuildViewRepository $buildRepository)
    {
        $this->transactionManager = $transactionManager;
        $this->buildRepository = $buildRepository;
    }

    public function handle(BuildEvent $event)
    {
        $build = $this->buildRepository->find($event->getBuildIdentifier());
        $attributes = $build->getRequest()->getAttributes();

        if (empty($attributes['tide_uuid'])) {
            throw new \RuntimeException('Build was created without a `tide_uuid` attribute');
        }

        $this->transactionManager->apply(Uuid::fromString($attributes['tide_uuid']), function (Tide $tide) use ($build) {
            /** @var BuildTask[] $tasks */
            $tasks = $tide->getTasks()->ofType(BuildTask::class);

            foreach ($tasks as $task) {
                $task->receiveBuildNotification($build);
            }
        });
    }
}
