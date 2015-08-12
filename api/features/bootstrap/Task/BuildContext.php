<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use ContinuousPipe\River\Task\Build\Event\BuildSuccessful;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use ContinuousPipe\River\Task\Task;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class BuildContext implements Context
{
    /**
     * @var \TideContext
     */
    private $tideContext;

    /**
     * @var \FlowContext
     */
    private $flowContext;

    /**
     * @var \Tide\TasksContext
     */
    private $tideTasksContext;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var BuilderBuild
     */
    private $lastBuild;

    /**
     * @param EventStore $eventStore
     * @param MessageBus $eventBus
     */
    public function __construct(EventStore $eventStore, MessageBus $eventBus)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
        $this->flowContext = $scope->getEnvironment()->getContext('FlowContext');
        $this->tideTasksContext = $scope->getEnvironment()->getContext('Tide\TasksContext');
    }

    /**
     * @When a build task is started
     */
    public function aBuildTaskIsStarted()
    {
        $this->flowContext->iHaveAFlowWithTheBuildTask();
        $this->tideContext->aTideIsStartedBasedOnThatWorkflow();
    }

    /**
     * @Then the build task should be failed
     */
    public function theBuildTaskShouldBeFailed()
    {
        $buildTask = $this->getBuildTask();
        if (!$buildTask->isFailed()) {
            throw new \RuntimeException('Expected the task to be failed');
        }
    }

    /**
     * @Then the build task should be running
     */
    public function theBuildTaskShouldBeRunning()
    {
        if (!$this->getBuildTask()->isRunning()) {
            throw new \RuntimeException('Expected the task to be running');
        }
    }

    /**
     * @Then the build task should be successful
     */
    public function theBuildTaskShouldBeSuccessful()
    {
        if (!$this->getBuildTask()->isSuccessful()) {
            throw new \RuntimeException('Expected the task to be successful');
        }
    }

    /**
     * @Then it should build the application images
     */
    public function itShouldBuildTheApplicationImages()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $imageBuildsStartedEvents = array_filter($events, function(TideEvent $event) {
            return $event instanceof ImageBuildsStarted;
        });

        if (1 !== count($imageBuildsStartedEvents)) {
            throw new \Exception(sprintf(
                'Found %d image builds started event, expected 1',
                count($imageBuildsStartedEvents)
            ));
        }
    }

    /**
     * @Then it should build the :number application images
     */
    public function itShouldBuildTheGivenNumberOfApplicationImages($number)
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $numberOfImageBuildStartedEvents = count(array_filter($events, function(TideEvent $event) {
            return $event instanceof BuildStarted;
        }));

        $number = (int) $number;
        if ($number !== $numberOfImageBuildStartedEvents) {
            throw new \Exception(sprintf(
                'Found %d image builds started event, expected %d',
                $numberOfImageBuildStartedEvents,
                $number
            ));
        }
    }

    /**
     * @Given an image build was started
     */
    public function anImageBuildWasStarted()
    {
        $this->lastBuild = new BuilderBuild(
            (string) Uuid::uuid1(),
            BuilderBuild::STATUS_PENDING
        );

        $this->eventStore->add(new BuildStarted(
            $this->tideContext->getCurrentTideUuid(),
            $this->lastBuild
        ));
    }

    /**
     * @When the build is failing
     */
    public function theBuildIsFailing()
    {
        $this->eventBus->handle(new BuildFailed(
            $this->tideContext->getCurrentTideUuid(),
            $this->lastBuild
        ));
    }

    /**
     * @Given :number images builds were started
     */
    public function imagesBuildsWereStarted($number)
    {
        while ($number-- > 0) {
            $this->anImageBuildWasStarted();
        }
    }

    /**
     * @When one image build is successful
     */
    public function oneImageBuildIsSuccessful()
    {
        $this->eventBus->handle(new BuildSuccessful(
            $this->tideContext->getCurrentTideUuid(),
            $this->lastBuild
        ));
    }

    /**
     * @Then the image builds should be waiting
     */
    public function theImageBuildsShouldBeWaiting()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $numberOfImagesBuiltEvents = count(array_filter($events, function(TideEvent $event) {
            return $event instanceof ImageBuildsSuccessful;
        }));

        if (0 !== $numberOfImagesBuiltEvents) {
            throw new \Exception(sprintf(
                'Found %d images built events, expected 0',
                $numberOfImagesBuiltEvents
            ));
        }

        try {
            $this->tideContext->theTideShouldBeFailed();
            $failed = true;
        } catch (\Exception $e) {
            $failed = false;
        }

        if ($failed) {
            throw new \RuntimeException('The tide is failed and wasn\'t expected to be');
        }
    }

    /**
     * @When one image build is failed
     */
    public function oneImageBuildIsFailed()
    {
        $this->theBuildIsFailing();
    }

    /**
     * @When :number image builds are successful
     */
    public function imageBuildsAreSuccessful($number)
    {
        while ($number-- > 0) {
            $this->oneImageBuildIsSuccessful();
        }
    }

    /**
     * @Then the image should be successfully built
     */
    public function theImagesShouldBeSuccessfullyBuilt()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $numberOfImagesBuiltEvents = count(array_filter($events, function(TideEvent $event) {
            return $event instanceof ImageBuildsSuccessful;
        }));

        if (1 !== $numberOfImagesBuiltEvents) {
            throw new \Exception(sprintf(
                'Found %d images built event, expected 1',
                $numberOfImagesBuiltEvents
            ));
        }
    }

    /**
     * @When all the image builds are successful
     */
    public function allTheImageBuildsAreSuccessful()
    {
        $tideUuid = $this->tideContext->getCurrentTideUuid();
        $events = $this->eventStore->findByTideUuid($tideUuid);

        /** @var ImageBuildsStarted[] $imageBuildsStartedEvents */
        $imageBuildsStartedEvents = array_filter($events, function(TideEvent $event) {
            return $event instanceof ImageBuildsStarted;
        });

        $imageBuildsStartedEvent = current($imageBuildsStartedEvents);
        $this->eventBus->handle(new ImageBuildsSuccessful($tideUuid, $imageBuildsStartedEvent->getLog()));
    }

    /**
     * @return BuildTask
     */
    private function getBuildTask()
    {
        /** @var Task[] $deployTasks */
        $buildTasks = $this->tideTasksContext->getTasksOfType(BuildTask::class);

        if (count($buildTasks) == 0) {
            throw new \RuntimeException('No build task found');
        }

        return current($buildTasks);
    }
}