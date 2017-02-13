<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
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

    public function __construct(
        TransactionManager $transactionManager,
        ArtifactRemover $artifactRemover
    ) {
        $this->transactionManager = $transactionManager;
        $this->artifactRemover = $artifactRemover;
    }

    public function notify($event)
    {
        if (!method_exists($event, 'getBuildIdentifier')) {
            throw new \InvalidArgumentException(sprintf(
                'The object of class "%s" do not have a `getBuildIdentifier` method',
                get_class($event)
            ));
        }

        $this->transactionManager->apply($event->getBuildIdentifier(), function (Build $build) use ($event) {
            if ($event instanceof StepFailed) {
                $build->fail();
            } elseif ($event instanceof BuildStarted) {
                $build->nextStep();
            } elseif ($event instanceof StepFinished) {
                $build->stepFinished($event);
            } elseif ($event instanceof BuildFinished || $event instanceof BuildFailed) {
                $build->cleanUp($this->artifactRemover);
            }
        });
    }
}
