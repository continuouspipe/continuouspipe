<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStep;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildCreated;
use ContinuousPipe\Builder\Aggregate\Event\BuildFailed;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildStepStarted;
use ContinuousPipe\Builder\Artifact;
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

    use ApplyEventCapability;
    use RaiseEventCapability;

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
     * @var Artifact[]
     */
    private $writtenArtifacts = [];

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
    }

    public function nextStep()
    {
        $nextStepIndex = $this->currentStepCursor + 1;
        $steps = $this->request->getSteps();

        if (!isset($steps[$nextStepIndex])) {
            $this->raiseAndApply(new BuildFinished($this->identifier));
            return;
        }

        $stepConfiguration = $steps[$nextStepIndex];
        $this->raiseAndApply(new BuildStepStarted($this->identifier, $nextStepIndex, $stepConfiguration));
    }

    public function fail()
    {
        $this->raiseAndApply(new BuildFailed(
            $this->identifier
        ));
    }

    public function cleanUp(Artifact\ArtifactRemover $artifactRemover)
    {
        foreach ($this->writtenArtifacts as $artifact) {
            try {
                $artifactRemover->remove($artifact);
            } catch (Artifact\ArtifactException $e) {
                throw $e;
                // Ignore if we weren't able to remove an artifact
            }
        }
    }

    private function applyBuildStepStarted(BuildStepStarted $started)
    {
        $this->currentStepCursor = $started->getStepPosition();

        foreach ($started->getStepConfiguration()->getWriteArtifacts() as $artifact) {
            $this->writtenArtifacts[] = $artifact;
        }
    }

    private function applyBuildCreated(BuildCreated $event)
    {
        $this->identifier = $event->getBuildIdentifier();
        $this->request = $event->getRequest();
        $this->user = $event->getUser();
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
