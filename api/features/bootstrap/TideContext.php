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
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Event\TideStarted;
use Symfony\Component\Yaml\Yaml;

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
        $this->flowContext->iHaveAFlowWithTheBuildAndDeployTasks();
        $this->createTide();
    }

    /**
     * @When a tide is started
     */
    public function aTideIsStarted()
    {
        if (null === $this->tideUuid) {
            $this->aTideIsCreated();
        }

        $this->startTide();
    }

    /**
     * @When the tide failed
     */
    public function theTideFailed()
    {
        $this->eventBus->handle(new TideFailed($this->tideUuid));
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
     * @Then the tide should be failed
     */
    public function theTideShouldBeFailed()
    {
        $numberOfImageBuildStartedEvents = count($this->getEventsOfType(TideFailed::class));

        if (1 !== $numberOfImageBuildStartedEvents) {
            throw new \Exception(sprintf(
                'Found %d tail failed event, expected 1',
                $numberOfImageBuildStartedEvents
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
        $numberOfTideStartedEvents = count($this->getEventsOfType(TideCreated::class));

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
     * @When a tide is started based on that workflow
     */
    public function aTideIsStartedBasedOnThatWorkflow()
    {
        $this->createTide();
        $this->eventBus->handle(new TideStarted(
            $this->tideUuid
        ));
    }

    /**
     * @Given a tide is started for the branch :branch
     */
    public function aTideIsStartedForTheBranch($branch)
    {
        $this->createTide($branch);
        $this->startTide();
    }

    /**
     * @Then the image tag :tag should be built
     */
    public function theImageTagShouldBeBuilt($tag)
    {
        $buildStartedEvents = $this->getEventsOfType(BuildStarted::class);
        $matchingEvents = array_filter($buildStartedEvents, function(BuildStarted $event) use ($tag) {
            $buildRequest = $event->getBuild()->getRequest();

            return $buildRequest->getImage()->getTag() == $tag;
        });

        if (count($matchingEvents) == 0) {
            throw new \RuntimeException('No matching build started events found');
        }
    }

    /**
     * @Then the deployed image tag should be :tag
     */
    public function theDeployedImageTagShouldBe($tag)
    {
        $deploymentStartedEvents = $this->getEventsOfType(DeploymentStarted::class);

        $componentImage = 'image0:'.$tag;
        $builtImages = array_map(function(DeploymentStarted $event) {
            $dockerComposeContents = $event->getDeployment()->getRequest()->getDockerComposeContents();
            $parsed = Yaml::parse($dockerComposeContents);
            $component = current($parsed);

            return $component['image'];
        }, $deploymentStartedEvents);

        if (!in_array($componentImage, $builtImages)) {
            throw new \RuntimeException(sprintf(
                'Image "%s" not found. Found %s',
                $componentImage,
                implode(', ', $builtImages)
            ));
        }
    }

    /**
     * @Then the deployed environment name should be prefixed by the flow identifier
     */
    public function theDeployedEnvironmentNameShouldBePrefixedByTheFlowIdentifier()
    {
        $deploymentStartedEvents = $this->getEventsOfType(DeploymentStarted::class);
        $environmentNames = array_map(function(DeploymentStarted $event) {
            return $event->getDeployment()->getRequest()->getEnvironmentName();
        }, $deploymentStartedEvents);

        $flowUuid = (string) $this->flowContext->getCurrentFlow()->getUuid();
        $matchingEnvironmentNames = array_filter($environmentNames, function($environmentName) use ($flowUuid) {
            return substr($environmentName, 0, strlen($flowUuid)) == $flowUuid;
        });

        if (count($matchingEnvironmentNames) == 0) {
            throw new \RuntimeException(sprintf(
                'No matching environment names found. Found %s',
                implode(', ', $environmentNames)
            ));
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
     * @param string $eventType
     * @return TideEvent[] array
     */
    private function getEventsOfType($eventType)
    {
        $events = $this->eventStore->findByTideUuid($this->tideUuid);

        return array_values(array_filter($events, function(TideEvent $event) use ($eventType) {
            return get_class($event) == $eventType || is_subclass_of($event, $eventType);
        }));
    }

    private function createTide($branch = 'master')
    {
        $flow = $this->flowContext->getCurrentFlow();

        $tide = $this->tideFactory->createFromCodeReference(
            $flow,
            new CodeReference(
                $flow->getContext()->getCodeRepository(),
                sha1($branch),
                $branch
            )
        );

        $this->tideUuid = $tide->getContext()->getTideUuid();

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }

    private function startTide()
    {
        $this->commandBus->handle(new StartTideCommand($this->tideUuid));
    }
}
