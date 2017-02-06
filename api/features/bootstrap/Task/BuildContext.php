<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\Builder\Client\BuilderException;
use ContinuousPipe\Builder\Client\HookableBuilderClient;
use ContinuousPipe\Builder\Client\TraceableBuilderClient;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestStep;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use ContinuousPipe\River\Task\Build\Event\BuildSuccessful;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use ContinuousPipe\River\Task\Task;
use JMS\Serializer\SerializerInterface;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

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
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var TraceableBuilderClient
     */
    private $traceableBuilderClient;
    /**
     * @var HookableBuilderClient
     */
    private $hookableBuilderClient;

    /**
     * @param EventStore $eventStore
     * @param MessageBus $eventBus
     * @param KernelInterface $kernel
     * @param SerializerInterface $serializer
     * @param TraceableBuilderClient $traceableBuilderClient
     * @param HookableBuilderClient $hookableBuilderClient
     */
    public function __construct(
        EventStore $eventStore,
        MessageBus $eventBus,
        KernelInterface $kernel,
        SerializerInterface $serializer,
        TraceableBuilderClient $traceableBuilderClient,
        HookableBuilderClient $hookableBuilderClient
    ) {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->kernel = $kernel;
        $this->serializer = $serializer;
        $this->traceableBuilderClient = $traceableBuilderClient;
        $this->hookableBuilderClient = $hookableBuilderClient;
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
     * @Given the build request will fail
     */
    public function theBuildRequestWillFail()
    {
        $this->theBuildRequestWillFailWithTheReason('Something went wrong');
    }

    /**
     * @Given the build request will fail with the reason :reason
     */
    public function theBuildRequestWillFailWithTheReason($reason)
    {
        $this->hookableBuilderClient->addHook(function() use ($reason) {
            throw new BuilderException($reason);
        });
    }

    /**
     * @When a build task is started
     */
    public function aBuildTaskIsStarted()
    {
        $this->tideContext->aTideIsStartedWithABuildTask();
    }

    /**
     * @Then the build task should be failed
     */
    public function theBuildTaskShouldBeFailed()
    {
        $buildTask = $this->getBuildTask();
        if ($buildTask->getStatus() != Task::STATUS_FAILED) {
            throw new \RuntimeException(sprintf(
                'Expected the task to be failed (%s)',
                $buildTask->getStatus()
            ));
        }
    }

    /**
     * @Then the build task should be running
     */
    public function theBuildTaskShouldBeRunning()
    {
        $buildTask = $this->getBuildTask();
        if ($buildTask->getStatus() !== Task::STATUS_RUNNING) {
            throw new \RuntimeException(sprintf(
                'Expected the task to be running (%s)',
                $buildTask->getStatus()
            ));
        }
    }

    /**
     * @Then the build task should be successful
     */
    public function theBuildTaskShouldBeSuccessful()
    {
        $buildTask = $this->getBuildTask();
        if ($buildTask->getStatus() !== Task::STATUS_SUCCESSFUL) {
            throw new \RuntimeException(sprintf(
                'Expected the task to be successful (%s)',
                $buildTask->getStatus()
            ));
        }
    }

    /**
     * @Then it should build the application images
     */
    public function itShouldBuildTheApplicationImages()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $imageBuildsStartedEvents = array_filter($events, function (TideEvent $event) {
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
        $numberOfImageBuildStartedEvents = count(array_filter($events, function (TideEvent $event) {
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
     * @When the build is failing
     * @When the build task failed
     */
    public function theBuildIsFailing()
    {
        $this->dispatchBuildStatus(
            $this->getLastBuild(),
            BuilderBuild::STATUS_ERROR
        );
    }

    /**
     * @When the build succeed
     */
    public function theBuildSucceed()
    {
        $this->dispatchBuildStatus(
            $this->getLastBuild(),
            BuilderBuild::STATUS_SUCCESS
        );
    }

    /**
     * @When the builds are failing
     */
    public function theBuildsAreFailing()
    {
        $imageBuildsStartedEvent = $this->getImageBuildsStartedEvent();

        $this->eventBus->handle(new ImageBuildsFailed(
            $this->tideContext->getCurrentTideUuid(),
            $imageBuildsStartedEvent->getTaskId(),
            $imageBuildsStartedEvent->getLog()
        ));
    }

    /**
     * @When one image build is successful
     */
    public function oneImageBuildIsSuccessful()
    {
        $this->dispatchBuildStatus($this->getLastBuild(), BuilderBuild::STATUS_SUCCESS);
    }

    /**
     * @Then the image builds should be waiting
     */
    public function theImageBuildsShouldBeWaiting()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $numberOfImagesBuiltEvents = count(array_filter($events, function (TideEvent $event) {
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
            $events = $this->getBuildStartedEvents();
            $firstEvent = $events[$number];

            $this->dispatchBuildStatus($firstEvent->getBuild(), BuilderBuild::STATUS_SUCCESS);
        }
    }

    /**
     * @When the first image build is successful
     * @When the first build task succeed
     */
    public function theFirstImageBuildIsSuccessful()
    {
        $events = $this->getBuildStartedEvents();
        $firstEvent = $events[0];

        $this->dispatchBuildStatus($firstEvent->getBuild(), BuilderBuild::STATUS_SUCCESS);
    }

    /**
     * @Then the image should be successfully built
     */
    public function theImagesShouldBeSuccessfullyBuilt()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $numberOfImagesBuiltEvents = count(array_filter($events, function (TideEvent $event) {
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
     * @When the build task succeed
     */
    public function allTheImageBuildsAreSuccessful()
    {
        foreach ($this->getBuildStartedEvents() as $event) {
            $this->dispatchBuildStatus(
                $event->getBuild(),
                BuilderBuild::STATUS_SUCCESS
            );
        }
    }

    /**
     * @Then the build should be started with Dockerfile path :path in the context
     * @Then the step #:stepIndex of the build should be started with the Dockerfile path :path
     */
    public function theBuildShouldBeStartedWithDockerfilePathInTheContext($path, $stepIndex = null)
    {
        $step = $this->getBuildRequestStep(null, $stepIndex);
        $foundPath = $step->getContext()->getDockerFilePath();

        if ($path != $foundPath) {
            throw new \RuntimeException(sprintf(
                'The path found is "%s" while expecting "%s"',
                $foundPath,
                $path
            ));
        }
    }

    /**
     * @Then the build should be started with the image name :imageName
     * @Then the step #:stepIndex of the build should be started with the image name :imageName
     */
    public function theBuildShouldBeStartedWithTheImageName($imageName, $stepIndex = null)
    {
        $step = $this->getBuildRequestStep(null, $stepIndex);
        $foundImageName = $step->getImage()->getName();

        if ($imageName != $foundImageName) {
            throw new \RuntimeException(sprintf(
                'The image name found is "%s" while expecting "%s"',
                $foundImageName,
                $imageName
            ));
        }
    }

    /**
     * @Then the build should be started with :numberOfSteps steps
     */
    public function theBuildShouldBeStartedWithSteps($numberOfSteps)
    {
        $steps = $this->getBuildRequest()->getSteps();

        if (count($steps) != $numberOfSteps) {
            throw new \RuntimeException(sprintf(
                'Expected %d steps but found %d instead',
                $numberOfSteps,
                count($steps)
            ));
        }
    }

    /**
     * @Then the build should be started with the sub-directory :path
     */
    public function theBuildShouldBeStartedWithTheSubDirectory($path)
    {
        $step = $this->getBuildRequestStep();
        $foundPath = $step->getContext()->getRepositorySubDirectory();

        if ($path != $foundPath) {
            throw new \RuntimeException(sprintf(
                'The path found is "%s" while expecting "%s"',
                $foundPath,
                $path
            ));
        }
    }

    /**
     * @Then the build should be started with the repository token :token
     */
    public function theBuildShouldBeStartedWithTheRepositoryToken($token)
    {
        $step = $this->getBuildRequestStep();
        $foundToken = $step->getRepository()->getToken();

        if ($token != $foundToken) {
            throw new \RuntimeException(sprintf(
                'The token found is "%s" while expecting "%s"',
                $foundToken,
                $token
            ));
        }
    }

    /**
     * @Then the build should be started with a BitBucket archive URL
     */
    public function theBuildShouldBeStartedWithABitbucketArchiveUrl()
    {
        $archive = $this->getBuildRequestStep()->getArchive();

        if (null === $archive) {
            throw new \RuntimeException('The archive is not found in the build request');
        }

        if (strpos($archive->getUrl(), 'bitbucket') === false) {
            throw new \RuntimeException(sprintf(
                '"bitbucket" not found in archive URL: %s',
                $archive->getUrl()
            ));
        }
    }

    /**
     * @Then the build should be started with an archive containing the :header header
     */
    public function theBuildShouldBeStartedWithAnArchiveContainingTheHeader($header)
    {
        $archive = $this->getBuildRequestStep()->getArchive();

        if (null === $archive) {
            throw new \RuntimeException('The archive is not found in the build request');
        }

        if (!array_key_exists($header, $archive->getHeaders())) {
            throw new \RuntimeException('Header not found');
        }
    }

    /**
     * @Then the build should be started with the following environment variables:
     * @Then the first build should be started with the following environment variables:
     */
    public function theBuildShouldBeStartedWithTheFollowingEnvironmentVariables(TableNode $environs)
    {
        $this->assertBuildIsStartedWithTheFollowingEnvironmentVariables(0, $environs);
    }

    /**
     * @Then the second build should be started with the following environment variables:
     */
    public function theSecondBuildShouldBeStartedWithTheFollowingEnvironmentVariables(TableNode $environs)
    {
        $this->assertBuildIsStartedWithTheFollowingEnvironmentVariables(1, $environs);
    }

    /**
     * @Then it should have sent a build request
     */
    public function itShouldHaveSentABuildRequest()
    {
        $requests = $this->traceableBuilderClient->getRequests();
        if (0 === count($requests)) {
            throw new \RuntimeException('Expected requests, found nothing');
        }
    }

    /**
     * @Then the step #:stepIndex of the build should be started with a :artifactType artifact identified :artifactIdentifier on path :path
     */
    public function theStepOfTheBuildShouldBeStartedWithAWriteArtifactIdentifiedOnPath($stepIndex, $artifactType, $artifactIdentifier, $path)
    {
        if ($artifactType === 'read') {
            $artifacts = $this->getBuildRequestStep(null, $stepIndex)->getReadArtifacts();
        } elseif ($artifactType == 'write') {
            $artifacts = $this->getBuildRequestStep(null, $stepIndex)->getWriteArtifacts();
        } else {
            throw new \RuntimeException(sprintf(
                'Artifact type %s not supported',
                $artifactType
            ));
        }

        foreach ($artifacts as $artifact) {
            if ($artifact->getIdentifier() == $artifactIdentifier && $artifact->getPath() == $path) {
                return;
            }
        }

        throw new \RuntimeException('Artifact not found');
    }

    private function assertBuildIsStartedWithTheFollowingEnvironmentVariables($index, TableNode $environs)
    {
        $step = $this->getBuildRequestStep($index);
        $environment = $step->getEnvironment();

        foreach ($environs->getHash() as $environ) {
            if (!array_key_exists($environ['name'], $environment)) {
                throw new \RuntimeException(sprintf(
                    'Environment variable named "%s" not found in request',
                    $environ['name']
                ));
            }

            if ($environment[$environ['name']] != $environ['value']) {
                throw new \RuntimeException(sprintf(
                    'Expected to find value "%s" in environment variable named "%s" but found "%s" instead',
                    $environ['value'],
                    $environ['name'],
                    $environment[$environ['name']]
                ));
            }
        }
    }

    /**
     * @return BuildTask
     */
    private function getBuildTask()
    {
        /* @var Task[] $deployTasks */
        $buildTasks = $this->tideTasksContext->getTasksOfType(BuildTask::class);

        if (count($buildTasks) == 0) {
            throw new \RuntimeException('No build task found');
        }

        return current($buildTasks);
    }

    /**
     * @return ImageBuildsStarted
     */
    private function getImageBuildsStartedEvent()
    {
        $events = $this->eventStore->findByTideUuid(
            $this->tideContext->getCurrentTideUuid()
        );

        /** @var ImageBuildsStarted[] $imageBuildsStartedEvents */
        $imageBuildsStartedEvents = array_filter($events, function (TideEvent $event) {
            return $event instanceof ImageBuildsStarted;
        });

        if (0 == count($imageBuildsStartedEvents)) {
            throw new \RuntimeException('No image build started event');
        }

        return current($imageBuildsStartedEvents);
    }

    /**
     * @return BuildStarted[]
     */
    private function getBuildStartedEvents()
    {
        $events = $this->eventStore->findByTideUuid(
            $this->tideContext->getCurrentTideUuid()
        );

        return array_values(array_filter($events, function (TideEvent $event) {
            return $event instanceof BuildStarted;
        }));
    }

    /**
     * @return BuilderBuild
     */
    private function getLastBuild()
    {
        if (count($this->getBuildStartedEvents()) == 0) {
            throw new \RuntimeException('No build started');
        }

        return $this->getBuildStartedEvents()[0]->getBuild();
    }

    /**
     * @param BuilderBuild $build
     * @param string $status
     */
    private function dispatchBuildStatus(BuilderBuild $build, string $status)
    {
        $build = new BuilderBuild(
            $build->getUuid(),
            $status,
            $build->getRequest()
        );

        $response = $this->kernel->handle(Request::create(
            '/builder/notification/tide/'. (string) $this->tideContext->getCurrentTideUuid(),
            'POST',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->serializer->serialize($build, 'json')
        ));

        if ($response->getStatusCode() != Response::HTTP_NO_CONTENT) {
            throw new \RuntimeException(sprintf(
                'Expected status code %d, got %d',
                Response::HTTP_NO_CONTENT,
                $response->getStatusCode()
            ));
        }
    }

    private function getBuildRequestStep($buildIndex = null, $stepIndex = null) : BuildRequestStep
    {
        $steps = $this->getBuildRequest($buildIndex)->getSteps();
        if (0 === count($steps)) {
            throw new \RuntimeException('No build step found');
        }

        if (null === $stepIndex) {
            return reset($steps);
        }

        return $steps[$stepIndex];
    }

    private function getBuildRequest($index = null): BuildRequest
    {
        $buildStartedEvents = $this->getBuildStartedEvents();
        if (count($buildStartedEvents) == 0) {
            throw new \RuntimeException('No build start events found');
        }

        if (null === $index) {
            $buildStartedEvent = current($buildStartedEvents);
        } else {
            $buildStartedEvent = $buildStartedEvents[$index];
        }

        $request = $buildStartedEvent->getBuild()->getRequest();

        return $request;
    }
}
