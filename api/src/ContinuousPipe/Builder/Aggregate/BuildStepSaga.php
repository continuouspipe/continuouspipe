<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\DockerImageBuilt;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Events\Transaction\TransactionManager;

class BuildStepSaga
{
    /**
     * @var Builder
     */
    private $builder;
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    public function __construct(Builder $builder, TransactionManager $transactionManager)
    {
        $this->builder = $builder;
        $this->transactionManager = $transactionManager;
    }

    public function notify($event)
    {
        if ($event instanceof StepStarted) {
            $exception = null;

            try {
                // FIXME $this->builder->build($event->getStepConfiguration());
            } catch (BuildException $exception) {
                // Will be handled later
            }

            $this->transactionManager->apply($event->getBuildIdentifier(), function (Build $build) use ($event, $exception) {
                $build->getStep($event->getStepPosition())->buildFinished($exception);
            });
        }

        if ($event instanceof DockerImageBuilt) {
            $exception = null;

            try {
                // FIXME $this->builder->push($event->getStepConfiguration());
            } catch (BuildException $exception) {
                // Will be handled later
            }

            $this->transactionManager->apply($event->getBuildIdentifier(), function (Build $build) use ($event, $exception) {
                $build->getStep($event->getStepPosition())->pushFinished($exception);
            });
        }
    }
}
