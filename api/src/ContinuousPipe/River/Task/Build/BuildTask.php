<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\Builder\BuilderException;
use ContinuousPipe\Builder\BuildRequestCreator;
use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use ContinuousPipe\River\Task\Build\Event\BuildSuccessful;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\TaskQueued;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class BuildTask extends EventDrivenTask
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var BuildContext
     */
    private $context;

    /**
     * @var BuildTaskConfiguration
     */
    private $configuration;

    /**
     * @var Log|null
     */
    private $log;

    /**
     * @var BuilderBuild[]
     */
    private $startedBuilds = [];

    /**
     * @var BuilderBuild[]
     */
    private $successfulBuilds = [];

    /**
     * @param EventCollection        $events
     * @param MessageBus             $commandBus
     * @param LoggerFactory          $loggerFactory
     * @param BuildContext           $context
     * @param BuildTaskConfiguration $configuration
     */
    public function __construct(EventCollection $events, MessageBus $commandBus, LoggerFactory $loggerFactory, BuildContext $context, BuildTaskConfiguration $configuration)
    {
        parent::__construct($context, $events);

        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->context = $context;
        $this->configuration = $configuration;
    }

    public function buildImages(BuildRequestCreator $buildRequestCreator)
    {
        $logger = $this->loggerFactory->from($this->context->getLog());
        $logger = $logger->child(new Text('Building application images'));
        $logger->updateStatus(Log::RUNNING);

        $this->context->setTaskLog($logger->getLog());
        $this->events->raiseAndApply(TaskQueued::fromContext($this->context));

        try {
            $buildRequests = $buildRequestCreator->createBuildRequests(
                $this->context->getTideUuid(),
                $this->context->getCodeReference(),
                $this->configuration,
                $this->context->getTeam()->getBucketUuid(),
                $logger->getLog()
            );
        } catch (BuilderException $e) {
            $logger->child(new Text($e->getMessage()));
            $this->events->raiseAndApply(new ImageBuildsFailed(
                $this->context->getTideUuid(),
                $this->getIdentifier(),
                $logger->getLog()
            ));

            return;
        }

        $this->events->raiseAndApply(new ImageBuildsStarted(
            $this->context->getTideUuid(),
            $this->getIdentifier(),
            $buildRequests,
            $logger->getLog()
        ));

        if (empty($buildRequests)) {
            $logger->child(new Text('Found no image to build'));
            $this->events->raiseAndApply(new ImageBuildsSuccessful(
                $this->context->getTideUuid(),
                $this->getIdentifier(),
                $logger->getLog()
            ));
        }
    }

    public function build(BuilderClient $client, BuildRequest $request)
    {
        $build = $client->build($request, $this->context->getUser());

        $this->events->raiseAndApply(new BuildStarted(
            $this->context->getTideUuid(),
            $this->getIdentifier(),
            $build
        ));
    }

    public function receiveBuildNotification(BuilderBuild $build)
    {
        if (!$this->hasStartedBuild($build)) {
            return;
        }

        if ($build->isSuccessful()) {
            $this->events->raiseAndApply(new BuildSuccessful(
                $this->context->getTideUuid(),
                $build
            ));

            if ($this->allImageBuildsSuccessful()) {
                $this->events->raiseAndApply(new ImageBuildsSuccessful(
                    $this->context->getTideUuid(),
                    $this->getIdentifier(),
                    $this->log
                ));
            }
        } elseif ($build->isErrored()) {
            $this->events->raiseAndApply(new BuildFailed(
                $this->context->getTideUuid(),
                $build
            ));

            $this->events->raiseAndApply(new ImageBuildsFailed(
                $this->context->getTideUuid(),
                $this->getIdentifier(),
                $this->log
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideEvent $event)
    {
        parent::apply($event);

        if ($event instanceof ImageBuildsStarted) {
            $this->log = $event->getLog();
        } elseif ($event instanceof BuildStarted) {
            $this->startedBuilds[] = $event->getBuild();
        } elseif ($event instanceof BuildSuccessful) {
            $this->successfulBuilds[] = $event->getBuild();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function accept(TideEvent $event)
    {
        if ($event instanceof BuildFailed || $event instanceof BuildSuccessful) {
            return $this->hasStartedBuild($event->getBuild());
        }

        return parent::accept($event);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return 0 < $this->numberOfEventsOfType(ImageBuildsSuccessful::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return 0 < $this->numberOfEventsOfType(ImageBuildsFailed::class);
    }

    private function allImageBuildsSuccessful() : bool
    {
        foreach ($this->startedBuilds as $build) {
            if (!$this->isBuildSuccessful($build)) {
                return false;
            }
        }

        return true;
    }

    private function isBuildSuccessful(BuilderBuild $build) : bool
    {
        foreach ($this->successfulBuilds as $successfulBuild) {
            if ($successfulBuild->getUuid() == $build->getUuid()) {
                return true;
            }
        }

        return false;
    }

    private function hasStartedBuild(BuilderBuild $build) : bool
    {
        foreach ($this->startedBuilds as $startedBuild) {
            if ($startedBuild->getUuid() == $build->getUuid()) {
                return true;
            }
        }

        return false;
    }
}
