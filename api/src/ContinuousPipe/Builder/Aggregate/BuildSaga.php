<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Events\Transaction\TransactionManager;

class BuildSaga
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
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
        }
    }
}
