<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\DockerImageBuilt;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepEvent;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildStepStarted;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Events\Transaction\TransactionManager;
use SimpleBus\Message\Bus\MessageBus;

class BuildStepSaga
{
    /**
     * @var Builder
     */
    private $builder;
    /**
     * @var BuildStepRepository
     */
    private $buildStepRepository;
    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(Builder $builder, BuildStepRepository $buildStepRepository, MessageBus $eventBus)
    {
        $this->builder = $builder;
        $this->buildStepRepository = $buildStepRepository;
        $this->eventBus = $eventBus;
    }

    public function notify($event)
    {
        if ($event instanceof BuildStepStarted) {
            $step = BuildStep::create(
                $event->getBuildIdentifier(),
                $event->getStepPosition(),
                $event->getStepConfiguration()
            );
        } else {
            $step = $this->notifyExistingStep($event);
        }

        foreach ($step->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }

    private function notifyExistingStep(StepEvent $event) : BuildStep
    {
        $step = $this->buildStepRepository->find($event->getBuildIdentifier(), $event->getStepPosition());

        if ($event instanceof StepStarted) {
            $step->buildFinished($this->catchAndReturnException(function() {
                // FIXME $this->builder->build($event->getStepConfiguration());
            }));
        }

        if ($event instanceof DockerImageBuilt) {
            $step->pushFinished($this->catchAndReturnException(function() {
                // FIXME $this->builder->push($event->getStepConfiguration());
            }));
        }

        return $step;
    }

    private function catchAndReturnException(callable $callable)
    {
        $exception = null;

        try {
            $callable();
        } catch (\Exception $exception) {
            // Returned after...
        }

        return $exception;
    }
}
