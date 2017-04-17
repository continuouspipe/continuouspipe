<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStep;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildCreated;
use ContinuousPipe\Builder\Aggregate\Event\BuildFailed;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildStepFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStepStarted;
use ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Event\GCBuildFinished;
use ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Event\GCBuildStarted;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuilderClient;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Events\Aggregate;
use ContinuousPipe\Events\Capabilities\ApplyEventCapability;
use ContinuousPipe\Events\Capabilities\RaiseEventCapability;
use ContinuousPipe\Security\User\User;
use Psr\Log\LoggerInterface;
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

    private $currentStepCursor = 0;
    private $status = self::STATUS_PENDING;

    private function __construct()
    {
    }

    public static function createFromRequest(BuildRequest $request, User $user, string $identifier = null) : Build
    {
        $build = new self();
        $build->raiseAndApply(new BuildCreated(
            $identifier ?: Uuid::uuid4()->toString(),
            $request,
            $user
        ));

        return $build;
    }

    public function startWithGoogleContainerBuilder(GoogleContainerBuilderClient $client)
    {
        $build = $client->createFromRequest($this);

        $this->raiseAndApply(new GCBuildStarted(
            $this->identifier,
            $build
        ));
    }

    public function googleContainerBuildFinished(GCBuildFinished $event)
    {
        if ($event->getStatus()->isSuccessful()) {
            $this->raiseAndApply(new BuildFinished($this->identifier));
        } else {
            $this->fail();
        }
    }

    public function start()
    {
        $this->raiseAndApply(new BuildStarted($this->identifier));
    }

    public function nextStep()
    {
        $steps = $this->request->getSteps();

        if (!isset($steps[$this->currentStepCursor])) {
            $this->raiseAndApply(new BuildFinished($this->identifier));
            return;
        }

        $stepConfiguration = $steps[$this->currentStepCursor];
        $this->raiseAndApply(new BuildStepStarted($this->identifier, $this->currentStepCursor, $stepConfiguration));
    }

    public function fail()
    {
        $this->raiseAndApply(new BuildFailed(
            $this->identifier
        ));
    }

    public function cleanUp(Artifact\ArtifactRemover $artifactRemover, LoggerInterface $logger)
    {
        foreach ($this->writtenArtifacts as $artifact) {
            if ($artifact->isPersistent()) {
                continue;
            }

            try {
                $artifactRemover->remove($artifact);
            } catch (Artifact\ArtifactException $e) {
                $logger->warning('Unable to delete an artifact', [
                    'build_identifier' => $this->identifier,
                    'exception' => $e,
                ]);
            }
        }
    }

    public function stepFinished(StepFinished $event)
    {
        $this->raiseAndApply(new BuildStepFinished(
            $event->getBuildIdentifier(),
            $event->getStepPosition()
        ));

        $this->nextStep();
    }

    public function isEngine(string $engine) : bool
    {
        return $this->request->getEngine() == new Engine($engine);
    }

    private function applyGCBuildStarted(GCBuildStarted $event)
    {}

    private function applyGCBuildFinished(GCBuildFinished $event)
    {}

    private function applyBuildStepStarted(BuildStepStarted $started)
    {
        $this->currentStepCursor = $started->getStepPosition();

        foreach ($started->getStepConfiguration()->getWriteArtifacts() as $artifact) {
            $this->writtenArtifacts[] = $artifact;
        }
    }

    private function applyBuildStepFinished(BuildStepFinished $finished)
    {
        $this->currentStepCursor = $finished->getStepPosition() + 1;
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
