<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildFailed;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Builder\Artifact\ArtifactRemover;
use ContinuousPipe\Events\Transaction\TransactionManager;

class BuildSaga
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;
    /**
     * @var ArtifactRemover
     */
    private $artifactRemover;

    public function __construct(TransactionManager $transactionManager, ArtifactRemover $artifactRemover)
    {
        $this->transactionManager = $transactionManager;
        $this->artifactRemover = $artifactRemover;
    }

    public function notify($event)
    {
        if ($event instanceof StepFailed) {
            $this->transactionManager->apply($event->getBuildIdentifier(), function (Build $build) {
                $build->fail();
            });
        } elseif ($event instanceof StepFinished || $event instanceof BuildStarted) {
            $this->transactionManager->apply($event->getBuildIdentifier(), function (Build $build) use ($event) {
                $build->nextStep();
            });
        } elseif ($event instanceof BuildFinished || $event instanceof BuildFailed) {
            $this->transactionManager->apply($event->getBuildIdentifier(), function (Build $build) use ($event) {
                $build->cleanUp($this->artifactRemover);
            });
        }
    }
}
