<?php

namespace ContinuousPipe\River\Bridge\Builder\Handler;

use ContinuousPipe\Builder\View\BuildViewRepository;
use ContinuousPipe\River\Bridge\Builder\Command\ReportImageBuildCompletion;
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;

class ReportImageBuildCompletionToAggregate
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var BuildViewRepository
     */
    private $buildRepository;

    public function __construct(TransactionManager $transactionManager, BuildViewRepository $buildRepository)
    {
        $this->transactionManager = $transactionManager;
        $this->buildRepository = $buildRepository;
    }

    public function handle(ReportImageBuildCompletion $command)
    {
        $build = $this->buildRepository->find($command->getBuildIdentifier());

        $this->transactionManager->apply($command->getTideUuid(), function (Tide $tide) use ($build) {
            /** @var BuildTask[] $tasks */
            $tasks = $tide->getTasks()->ofType(BuildTask::class);

            foreach ($tasks as $task) {
                $task->receiveBuildNotification($build);
            }
        });
    }
}
