<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Flow;
use ContinuousPipe\User\User;
use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use GitHub\WebHook\Model\Repository as GitHubRepository;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use ContinuousPipe\River\Tests\CodeRepository\FakeFileSystemResolver;
use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\BuildSuccessful;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class TideContext implements Context
{
    /**
     * @var FlowContext
     */
    private $flowContext;

    /**
     * @var Uuid|null
     */
    private $tideUuid;

    /**
     * @var Tide|null
     */
    private $tide;

    /**
     * @var BuilderBuild|null
     */
    private $lastBuild;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var EventStore
     */
    private $eventStore;
    
    /**
     * @var FakeFileSystemResolver
     */
    private $fakeFileSystemResolver;
    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var \ContinuousPipe\River\TideFactory
     */
    private $tideFactory;
    /**
     * @var \ContinuousPipe\River\View\TideRepository
     */
    private $viewTideRepository;

    /**
     * @param MessageBus $commandBus
     * @param MessageBus $eventBus
     * @param EventStore $eventStore
     * @param FakeFileSystemResolver $fakeFileSystemResolver
     * @param TideFactory $tideFactory
     * @param TideRepository $viewTideRepository
     */
    public function __construct(MessageBus $commandBus, MessageBus $eventBus, EventStore $eventStore, FakeFileSystemResolver $fakeFileSystemResolver, TideFactory $tideFactory, TideRepository $viewTideRepository)
    {
        $this->commandBus = $commandBus;
        $this->eventStore = $eventStore;
        $this->fakeFileSystemResolver = $fakeFileSystemResolver;
        $this->eventBus = $eventBus;
        $this->tideFactory = $tideFactory;
        $this->viewTideRepository = $viewTideRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->flowContext = $scope->getEnvironment()->getContext('FlowContext');
    }

    /**
     * @When a tide is created
     */
    public function aTideIsCreated()
    {
        $repository = new GitHubCodeRepository(
            new GitHubRepository('foo', 'http://github.com/foo/bar')
        );

        $this->tide = $this->tideFactory->createFromCodeReference(
            $this->flowContext->getOrCreateDefaultFlow($repository),
            new CodeReference($repository, 'master')
        );

        $this->tideUuid = $this->tide->getContext()->getTideUuid();
        foreach ($this->tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }

    /**
     * @When a tide is started
     */
    public function aTideIsStarted()
    {
        if (null === $this->tideUuid) {
            $this->aTideIsCreated();
        }

        $this->commandBus->handle(new StartTideCommand($this->tideUuid));
    }

    /**
     * @When the tide failed
     */
    public function theTideFailed()
    {
        $this->eventBus->handle(new TideFailed($this->tideUuid));
    }

    /**
     * @Then it should build the application images
     */
    public function itShouldBuildTheApplicationImages()
    {
        $events = $this->eventStore->findByTideUuid($this->tideUuid);
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
     * @Given there is :number application images in the repository
     */
    public function thereIsApplicationImagesInTheRepository($number)
    {
        $dockerComposeFile = '';
        for ($i = 0; $i < $number; $i++) {
            $dockerComposeFile .=
                'image'.$i.':'.PHP_EOL.
                '    build: ./'.$i.PHP_EOL.
                '    labels:'.PHP_EOL.
                '        com.continuouspipe.image-name: image'.$i.PHP_EOL;
        }

        $this->fakeFileSystemResolver->prepareFileSystem([
            'docker-compose.yml' => $dockerComposeFile
        ]);
    }

    /**
     * @Then it should build the :number application images
     */
    public function itShouldBuildTheGivenNumberOfApplicationImages($number)
    {
        $events = $this->eventStore->findByTideUuid($this->tideUuid);
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
            $this->tideUuid,
            $this->lastBuild
        ));
    }

    /**
     * @When the build is failing
     */
    public function theBuildIsFailing()
    {
        $this->eventBus->handle(new BuildFailed(
            $this->tideUuid,
            $this->lastBuild
        ));
    }

    /**
     * @Then the tide should be failed
     */
    public function theTideShouldBeFailed()
    {
        $events = $this->eventStore->findByTideUuid($this->tideUuid);
        $numberOfImageBuildStartedEvents = count(array_filter($events, function(TideEvent $event) {
            return $event instanceof TideFailed;
        }));

        if (1 !== $numberOfImageBuildStartedEvents) {
            throw new \Exception(sprintf(
                'Found %d tail failed event, expected 1',
                $numberOfImageBuildStartedEvents
            ));
        }
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
            $this->tideUuid,
            $this->lastBuild
        ));
    }

    /**
     * @Then the image builds should be waiting
     */
    public function theImageBuildsShouldBeWaiting()
    {
        $events = $this->eventStore->findByTideUuid($this->tideUuid);
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
            $this->theTideShouldBeFailed();
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
        $events = $this->eventStore->findByTideUuid($this->tideUuid);
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
     * @Then a tide view representation should have be created
     */
    public function aTideViewRepresentationShouldHaveBeCreated()
    {
        $this->viewTideRepository->find($this->tideUuid);
    }

    /**
     * @Then the tide is represented as pending
     */
    public function theTideIsRepresentedAsPending()
    {
        $this->assertTideStatusIs(ContinuousPipe\River\View\Tide::STATUS_PENDING);
    }

    /**
     * @Then the tide is represented as running
     */
    public function theTideIsRepresentedAsRunning()
    {
        $this->assertTideStatusIs(ContinuousPipe\River\View\Tide::STATUS_RUNNING);
    }

    /**
     * @Then the tide is represented as failed
     */
    public function theTideIsRepresentedAsFailed()
    {
        $this->assertTideStatusIs(ContinuousPipe\River\View\Tide::STATUS_FAILURE);
    }

    /**
     * @When the tide is successful
     */
    public function theTideIsSuccessful()
    {
        $this->eventBus->handle(new TideSuccessful($this->tideUuid));
    }

    /**
     * @Then the tide should be created
     */
    public function theTideShouldBeCreated()
    {
        $events = $this->eventStore->findByTideUuid($this->tideUuid);
        $numberOfTideStartedEvents = count(array_filter($events, function(TideEvent $event) {
            return $event instanceof TideCreated;
        }));

        if (0 === $numberOfTideStartedEvents) {
            throw new \RuntimeException('Tide started event not found');
        }
    }

    /**
     * @param string $status
     * @throws \RuntimeException
     */
    private function assertTideStatusIs($status)
    {
        $tide = $this->viewTideRepository->find($this->tideUuid);
        if ($tide->getStatus() != $status) {
            throw new \RuntimeException(sprintf('Found status "%s" instead', $tide->getStatus()));
        }
    }

    /**
     * @return null|Uuid
     */
    public function getCurrentTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @param Uuid $uuid
     */
    public function setCurrentTideUuid($uuid)
    {
        $this->tideUuid = $uuid;
    }

    /**
     * @return \ContinuousPipe\River\Tide
     */
    public function getCurrentTide()
    {
        return $this->tide;
    }
}
