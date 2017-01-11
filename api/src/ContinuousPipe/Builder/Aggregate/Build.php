<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildCreated;
use ContinuousPipe\Builder\Aggregate\Event\BuildFailed;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Events\Aggregate;
use ContinuousPipe\Events\Capabilities\ApplyEventCapability;
use ContinuousPipe\Events\Capabilities\RaiseEventCapability;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;

class Build implements Aggregate
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    use ApplyEventCapability{
        apply as buildApply;
    }
    use RaiseEventCapability {
        raisedEvents as buildRaisedEvents;
    }

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var BuildRequest
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var BuildStep[]
     */
    private $steps = [];
    private $currentStepCursor = -1;
    private $status = self::STATUS_PENDING;

    private function __construct()
    {
    }

    public static function createFromRequest(BuildRequest $request, User $user) : Build
    {
        $build = new self();
        $build->raiseAndApply(new BuildCreated(
            Uuid::uuid4()->toString(),
            $request,
            $user
        ));

        return $build;
    }

    public function start()
    {
        $this->raiseAndApply(new BuildStarted($this->identifier));
        $this->nextStep();
    }

    public function nextStep()
    {
        $nextStepIndex = $this->currentStepCursor + 1;

        if (isset($this->steps[$nextStepIndex])) {
            $this->steps[$nextStepIndex]->start();
        } else {
            $this->raiseAndApply(new BuildFinished($this->identifier));
        }
    }

    public function getStep(int $stepPosition) : BuildStep
    {
        return $this->steps[$stepPosition];
    }

    public function fail()
    {
        $this->raiseAndApply(new BuildFailed(
            $this->identifier
        ));
    }

    public function apply($event)
    {
        try {
            $this->buildApply($event);
        } catch (\BadMethodCallException $e) {
            // We don't care if we don't want to handle this event...
        }
    }

    private function applyStepStarted(StepStarted $started)
    {
        $this->currentStepCursor = $started->getStepPosition();
    }

    private function applyBuildCreated(BuildCreated $event)
    {
        $this->identifier = $event->getBuildIdentifier();
        $this->request = $event->getRequest();
        $this->user = $event->getUser();

        foreach ($this->request->getSteps() as $position => $configuration) {
            $this->steps[] = new BuildStep($this->identifier, $position, $configuration);
        }
    }

    private function applyBuildStarted()
    {
        $this->status = self::STATUS_RUNNING;
    }

    private function applyBuildFailed()
    {
        $this->status = self::STATUS_ERROR;
    }

    private function applyBuildFinished()
    {
        $this->status = self::STATUS_SUCCESS;
    }

    public function raisedEvents(): array
    {
        $events = $this->buildRaisedEvents();

        foreach ($this->steps as $step) {
            foreach ($step->raisedEvents() as $event) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return BuildRequest
     */
    public function getRequest(): BuildRequest
    {
        return $this->request;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    private function raiseAndApply($event)
    {
        $this->raise($event);
        $this->apply($event);
    }
}
