<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Message\Debug\TracedMessageProducer;
use ContinuousPipe\Message\Direct\DelayedMessagesBuffer;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Command\DeleteEnvironments;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveTideCommand;
use ContinuousPipe\River\Pipeline\Command\GenerateTides;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Pipeline\TideGenerationTrigger;
use ContinuousPipe\River\Recover\CancelTides\Command\CancelTideCommand;
use ContinuousPipe\River\Recover\TimedOutTides\Command\SpotTimedOutTidesCommand;
use ContinuousPipe\River\Tests\View\PredictableTimeResolver;
use ContinuousPipe\River\Tide\Concurrency\Command\RunPendingTidesCommand;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideTaskView;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use LogStream\Tree\TreeLog;
use phpseclib\Crypt\Random;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\MessageBus;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Event\TideStarted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
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
     * @var \TideConfigurationContext
     */
    private $tideConfigurationContext;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;
    /**
     * @var Tide|null
     */
    private $view;

    /**
     * @var PredictableTimeResolver
     */
    private $predictableTimeResolver;
    /**
     * @var \ContinuousPipe\River\Repository\TideRepository
     */
    private $tideRepository;
    /**
     * @var TracedMessageProducer
     */
    private $tracedMessageProducer;
    /**
     * @var DelayedMessagesBuffer
     */
    private $delayedMessagesBuffer;

    public function __construct(
        MessageBus $commandBus,
        MessageBus $eventBus,
        EventStore $eventStore,
        TideFactory $tideFactory,
        TideRepository $viewTideRepository,
        Kernel $kernel,
        TracedMessageProducer $tracedMessageProducer,
        PredictableTimeResolver $predictableTimeResolver,
        \ContinuousPipe\River\Repository\TideRepository $tideRepository,
        DelayedMessagesBuffer $delayedMessagesBuffer
    ) {
        $this->commandBus = $commandBus;
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->tideFactory = $tideFactory;
        $this->viewTideRepository = $viewTideRepository;
        $this->kernel = $kernel;
        $this->predictableTimeResolver = $predictableTimeResolver;
        $this->tideRepository = $tideRepository;
        $this->tracedMessageProducer = $tracedMessageProducer;
        $this->delayedMessagesBuffer = $delayedMessagesBuffer;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->flowContext = $scope->getEnvironment()->getContext('FlowContext');
        $this->tideConfigurationContext = $scope->getEnvironment()->getContext('TideConfigurationContext');
    }

    /**
     * @Transform :datetime
     */
    public function transformDateTime($value)
    {
        return \DateTime::createFromFormat(\DateTime::ISO8601, $value);
    }

    /**
     * @When the delayed messages are received
     */
    public function theDelayedMessagesAreReceived()
    {
        $this->delayedMessagesBuffer->flushDelayedMessages();
    }

    /**
     * @When a tide is created
     * @When a tide is created for the branch :branch
     * @Given a tide is created for branch :branch and commit :sha
     */
    public function aTideIsCreatedForBranchAndCommit($branch = null, $sha = null, Uuid $uuid = null)
    {
        if (null === $this->flowContext->getCurrentFlow()) {
            $this->flowContext->iHaveAFlow();
        }

        $this->createTide($branch ?: 'master', $sha, $uuid);
    }

    /**
     * @Given a tide is created for branch :branch and commit :sha with a deploy task
     */
    public function aTideIsCreatedForBranchAndCommitWithADeployTask($branch, $sha)
    {
        $continuousPipeFile = <<<EOF
tasks:
    - deploy:
          cluster: fake/provider
          services: []
EOF;

        $this->flowContext->iHaveAFlow();
        $this->flowContext->getCodeRepositoryContext()->thereIsAFileContaining(
            'continuous-pipe.yml',
            $continuousPipeFile
        );

        $this->createTide($branch, $sha);
    }

    /**
     * @Given a tide is started for branch :branch and commit :commit with a deploy task
     */
    public function aTideIsStartedForBranchAndCommitWithADeployTask($branch, $commit)
    {
        $this->aTideIsCreatedForBranchAndCommitWithADeployTask($branch, $commit);
        $this->startTide();
    }

    /**
     * @When a tide is started
     * @When a tide is started with the UUID :uuid
     * @When a tide is started with the UUID :uuid and branch :branch
     */
    public function aTideIsStarted($uuid = null, $branch = null)
    {
        if (null === $this->tideUuid || $uuid !== null) {
            $this->aTideIsCreatedForBranchAndCommit($branch, null, null !== $uuid ? Uuid::fromString($uuid) : null);
        }

        $this->startTide();
    }

    /**
     * @When the tide starts
     */
    public function startTide()
    {
        $this->commandBus->handle(new StartTideCommand($this->getTideUuid()));
    }

    /**
     * @When the second tide starts
     */
    public function theSecondTideStarts()
    {
        $flow = $this->flowContext->getCurrentFlow();
        $tides = $this->viewTideRepository->findLastByFlowUuid($flow->getUuid(), 1);
        if (count($tides) == 0) {
            throw new \RuntimeException(sprintf(
                'Found not tide in flow %s',
                $flow->getUuid()
            ));
        }

        $this->tideUuid = $tides[0]->getUuid();
        $this->commandBus->handle(new StartTideCommand($this->tideUuid));
    }

    /**
     * @When the tide for the branch :branch and commit :sha1 is tentatively started
     */
    public function theTideForCommitIsTentativelyStarted($branch, $sha1)
    {
        $tide = $this->getTideByCodeReference($branch, $sha1);

        $this->commandBus->handle(new StartTideCommand($tide->getUuid()));
    }

    /**
     * @When the tide for branch :branch and commit :sha1 is successful
     */
    public function theTideForCommitIsSuccessful($branch, $sha1)
    {
        $tide = $this->getTideByCodeReference($branch, $sha1);

        $this->eventBus->handle(new TideSuccessful($tide->getUuid()));
    }

    /**
     * @When the tide for branch :branch and commit :sha1 is cancelled
     */
    public function theTideForCommitIsCancelled($branch, $sha1)
    {
        $tide = $this->getTideByCodeReference($branch, $sha1);

        $this->commandBus->handle(new CancelTideCommand($tide->getUuid()));
    }

    /**
     * @When the tide failed
     * @When the tide is failing because :reason
     */
    public function theTideFailed($reason = null)
    {
        $this->eventBus->handle(new TideFailed($this->tideUuid, $reason ?: 'TideContext reason'));
    }

    /**
     * @When I cancel the tide
     */
    public function iCancelTheTide($uuid = null)
    {
        $response = $this->kernel->handle(Request::create(
            sprintf('/tides/%s/cancel', $uuid ?: (string) $this->getCurrentTideUuid()),
            'POST'
        ));

        if ($response->getStatusCode() != 204) {
            echo $response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code 200, got %d',
                $response->getStatusCode()
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

        $this->flowContext->getCodeRepositoryContext()->thereIsAFileContaining(
            'docker-compose.yml',
            $dockerComposeFile
        );
    }

    /**
     * @Given there is an application image in the repository with Dockerfile path :path
     */
    public function thereIsAnApplicationImageInTheRepositoryWithDockerfilePath($path)
    {
        $this->flowContext->getCodeRepositoryContext()->thereIsAFileContaining(
            'docker-compose.yml',
            'image:'.PHP_EOL.
            '    build: .'.PHP_EOL.
            '    dockerfile: '.$path.PHP_EOL.
            '    labels:'.PHP_EOL.
            '        com.continuouspipe.image-name: image'.PHP_EOL
        );
    }

    /**
     * @Then the tide should be failed
     */
    public function theTideShouldBeFailed()
    {
        $numberOfTideFailedEvents = count($this->getEventsOfType(TideFailed::class));

        if (1 !== $numberOfTideFailedEvents) {
            throw new \Exception(sprintf(
                'Found %d tide failed event, expected 1',
                $numberOfTideFailedEvents
            ));
        }
    }

    /**
     * @Then the tide should not be failed
     */
    public function theTideShouldNotBeFailed()
    {
        $numberOfTideFailedEvents = count($this->getEventsOfType(TideFailed::class));

        if (0 !== $numberOfTideFailedEvents) {
            throw new \Exception(sprintf(
                'Found %d tide failed event, expected 0',
                $numberOfTideFailedEvents
            ));
        }
    }

    /**
     * @Then the tide should be cancelled
     */
    public function theTideShouldBeCancelled()
    {
        $tide = $this->getCurrentTide();
        if ($tide->getStatus() !== Tide::STATUS_CANCELLED) {
            throw new \Exception(sprintf(
                'Found status "%s", expected "%s"',
                $tide->getStatus(),
                Tide::STATUS_CANCELLED
            ));
        }
    }

    /**
     * @Then the tide should be successful
     */
    public function theTideShouldBeSuccessful()
    {
        $numberOfTideSuccessfulEvents = count($this->getEventsOfType(TideSuccessful::class));

        if (1 !== $numberOfTideSuccessfulEvents) {
            throw new \Exception(sprintf(
                'Found %d tide successful event, expected 1',
                $numberOfTideSuccessfulEvents
            ));
        }
    }

    /**
     * @Then the tide should be running
     */
    public function theTideShouldBeRunning()
    {
        $numberOfTideFailedEvents = count($this->getEventsOfType(TideFailed::class));
        $numberOfTideSuccessfulEvents = count($this->getEventsOfType(TideSuccessful::class));
        $numberOfTideStartedEvents = count($this->getEventsOfType(TideStarted::class));

        if (1 !== $numberOfTideStartedEvents) {
            throw new \Exception(sprintf(
                'Found %d tide started event, expected 1',
                $numberOfTideStartedEvents
            ));
        }

        if (0 !== $numberOfTideSuccessfulEvents || 0 !== $numberOfTideFailedEvents) {
            throw new \Exception(sprintf(
                'Found tide failed event (%d) or tide successful (%d), expected 0',
                $numberOfTideFailedEvents,
                $numberOfTideSuccessfulEvents
            ));
        }
    }

    /**
     * @When I retrieve the list tides of the flow :flowUuid
     */
    public function iRetrieveTheListTidesOfTheFlow($flowUuid)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/flows/%s/tides', $flowUuid)
        ));
    }

    /**
     * @When I retrieve the list tides of the flow :flowUuid with a limit of :limit tides
     */
    public function iRetrieveTheListTidesOfTheFlowWithALimitOfTides($flowUuid, $limit)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/flows/%s/tides', $flowUuid),
            'GET',
            [
                'limit' => $limit,
            ]
        ));
    }

    /**
     * @When I retrieve the page :page of the list of tides of the flow :flowUuid with a limit of :limit tides
     */
    public function iRetrieveThePageOfTheListOfTidesOfTheFlowWithALimitOfTides($page, $flowUuid, $limit)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/flows/%s/tides', $flowUuid),
            'GET',
            [
                'limit' => $limit,
                'page' => $page,
            ]
        ));
    }

    /**
     * @When I request the tide view
     */
    public function iRequestTheTideView()
    {
        $this->view = $this->viewTideRepository->find($this->tideUuid);
    }

    /**
     * @Then the task :task should be :status
     */
    public function theTaskShouldBe($taskIdentifier, $status)
    {
        if (null === $this->view) {
            $this->iRequestTheTideView();
        }

        $matchingTasks = array_filter($this->view->getTasks(), function(TideTaskView $view) use ($taskIdentifier) {
            return $view->getIdentifier() == $taskIdentifier;
        });

        if (count($matchingTasks) == 0) {
            throw new \RuntimeException('No task matching this identifier found');
        }

        $task = current($matchingTasks);
        $foundStatus = $task->getStatus();

        if ($status != $foundStatus) {
            throw new \RuntimeException(sprintf(
                'Found status "%s" while expecting "%s"',
                $foundStatus,
                $status
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
     * @Then the tide view should contain a pipeline
     */
    public function theTideViewShouldContainAPipeline()
    {
        $view = $this->viewTideRepository->find($this->tideUuid);

        if (null === $view->getPipeline()) {
            throw new \RuntimeException('Found a `null` pipeline for that tide');
        }
    }

    /**
     * @Then the tide is represented as pending
     */
    public function theTideIsRepresentedAsPending()
    {
        $this->assertTideStatusIs(Tide::STATUS_PENDING);
    }

    /**
     * @Then the tide is represented as running
     */
    public function theTideIsRepresentedAsRunning()
    {
        $this->assertTideStatusIs(Tide::STATUS_RUNNING);
    }

    /**
     * @Then the tide is represented as failed
     */
    public function theTideIsRepresentedAsFailed()
    {
        $this->assertTideStatusIs(Tide::STATUS_FAILURE);
    }

    /**
     * @Then the tide is represented as successful
     */
    public function theTideIsRepresentedAsSuccessful()
    {
        $this->assertTideStatusIs(Tide::STATUS_SUCCESS);
    }

    /**
     * @When the current datetime is :datetime
     */
    public function theCurrentDatetimeIs(\DateTime $datetime)
    {
        $this->predictableTimeResolver->setCurrent($datetime);
    }

    /**
     * @Then the tide creation date should be :datetime
     */
    public function theTideCreationDateShouldBe(\DateTime $datetime)
    {
        $tide = $this->viewTideRepository->find($this->tideUuid);

        $this->assertDateEquals($datetime, $tide->getCreationDate());
    }

    /**
     * @Then the tide start date should be :datetime
     */
    public function theTideStartDateShouldBe(\DateTime $datetime)
    {
        $tide = $this->viewTideRepository->find($this->tideUuid);

        $this->assertDateEquals($datetime, $tide->getStartDate());
    }

    /**
     * @Then the tide finish date should be :datetime
     */
    public function theTideFinishDateShouldBe(\DateTime $datetime)
    {
        $tide = $this->viewTideRepository->find($this->tideUuid);

        $this->assertDateEquals($datetime, $tide->getFinishDate());
    }

    /**
     * @When the tide is successful
     */
    public function theTideIsSuccessful()
    {
        $this->eventBus->handle(new TideSuccessful($this->tideUuid));
    }

    /**
     * @When the tide is cancelled
     */
    public function theTideIsCancelled()
    {
        $this->commandBus->handle(new CancelTideCommand(
            $this->tideUuid
        ));
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
     * @Then only :count tide should be created
     */
    public function onlyTideShouldBeCreated($count)
    {
        $flow = $this->flowContext->getCurrentFlow();
        $tides = $this->viewTideRepository->findByFlowUuid($flow->getUuid());
        $numberOfTideStarted = count($tides->toArray());

        if ($count != $numberOfTideStarted) {
            throw new \RuntimeException(sprintf(
                '%d tide started events found, expected %d',
                $numberOfTideStarted,
                $count
            ));
        }
    }

    /**
     * @Then the tide should not be created
     */
    public function theTideShouldNotBeCreated()
    {
        try {
            $numberOfTideStartedEvents = count($this->getEventsOfType(TideCreated::class));
        } catch (\RuntimeException $e) {
            // We do not need to do anything as that means that the script had no way to find a tide :)
            echo $e->getMessage();

            return;
        }

        if ($numberOfTideStartedEvents > 0) {
            throw new \RuntimeException('Tide started event found');
        }
    }

    /**
     * @param string $status
     *
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
        $this->commandBus->handle(new StartTideCommand(
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
     * @When a tide is started for the branch :branch and commit :sha1
     */
    public function aTideIsStartedForTheBranchAndCommit($branch, $sha1)
    {
        $this->createTide($branch, $sha1);
        $this->startTide();
    }

    /**
     * @Then the image tag :tag should be built
     */
    public function theImageTagShouldBeBuilt($tag)
    {
        $foundTags = [];

        foreach ($this->getEventsOfType(BuildStarted::class) as $event) {
            $buildRequest = $event->getBuild()->getRequest();

            foreach ($buildRequest->getSteps() as $step) {
                $foundTags[] = $step->getImage()->getTag();
            }
        }

        if (false === array_search($tag, $foundTags)) {
            throw new \RuntimeException(sprintf(
                'No built request for tag "%s" found but found %s',
                $tag,
                implode(', ', $foundTags)
            ));
        }
    }

    /**
     * @Then the deployed image named :name should should be tagged :tag
     */
    public function theDeployedImageTagShouldBe($name, $tag)
    {
        $deploymentStartedEvents = $this->getEventsOfType(DeploymentStarted::class);
        $matchingDeployments = array_map(function (DeploymentStarted $event) use ($name, $tag) {
            $components = $event->getDeployment()->getRequest()->getSpecification()->getComponents();
            $component = $components[0];
            $source = $component->getSpecification()->getSource();

            return $source->getImage() == $name && $source->getTag() == $tag;
        }, $deploymentStartedEvents);

        if (0 === count($matchingDeployments)) {
            throw new \RuntimeException(sprintf(
                'Image "%s" tagged "%s" not found.',
                $name,
                $tag
            ));
        }
    }

    /**
     * @Then the deployed image name should be :name
     */
    public function theDeployedImageNameShouldBe($name)
    {

    }

    /**
     * @Then the deployed environment name should be prefixed by the flow identifier
     */
    public function theDeployedEnvironmentNameShouldBePrefixedByTheFlowIdentifier()
    {
        $deploymentStartedEvents = $this->getEventsOfType(DeploymentStarted::class);
        $environmentNames = array_map(function (DeploymentStarted $event) {
            return $event->getDeployment()->getRequest()->getTarget()->getEnvironmentName();
        }, $deploymentStartedEvents);

        $flowUuid = (string) $this->flowContext->getCurrentFlow()->getUuid();
        $matchingEnvironmentNames = array_filter($environmentNames, function ($environmentName) use ($flowUuid) {
            return substr($environmentName, 0, strlen($flowUuid)) == $flowUuid;
        });

        if (count($matchingEnvironmentNames) == 0) {
            throw new \RuntimeException(sprintf(
                'No matching environment names found. Found %s',
                implode(', ', $environmentNames)
            ));
        }
    }

    public function aTideIsStartedWithTasks(array $tasks, string $branch = 'master')
    {
        $configuration = [
            'tasks' => $tasks
        ];

        $this->aTideIsStartedWithConfiguration($configuration, $branch);
    }

    /**
     * @When a tide is started with a build and deploy task
     */
    public function aTideIsStartedWithABuildAndDeployTask()
    {
        $this->aTideIsStartedWithTasks([
            'build' => [
                'build' => []
            ],
            'deploy' => [
                'deploy' => [
                    'cluster' => 'fake/foo'
                ]
            ]
        ]);
    }

    /**
     * @When a tide is started with a build task
     */
    public function aTideIsStartedWithABuildTask()
    {
        $this->aTideIsStartedWithTasks([
            'build' => [
                'build' => []
            ]
        ]);
    }

    /**
     * @When a tide is started with a build task that have the following environment variables:
     */
    public function aTideIsStartedWithABuildTaskThatHaveTheFollowingEnvironmentVariables(TableNode $environ)
    {
        $this->aTideIsStartedWithTasks([
            'build' => [
                'build' => [
                    'environment' => $environ->getHash(),
                    'services' => ['image0' => []]
                ]
            ]
        ]);
    }

    /**
     * @When a tide is started for the branch :branch with a build and deploy task
     */
    public function aTideIsStartedForTheBranchWithABuildAndDeployTask($branch)
    {
        $this->aTideIsStartedWithTasks([
            [
                'build' => []
            ],
            [
                'deploy' => [
                    'cluster' => 'fake/foo'
                ]
            ]
        ], $branch);
    }

    /**
     * @When a tide is started for the branch :branch with a deploy task
     * @When a tide is started for the branch :branch with a deploy task for the cluster :cluster
     */
    public function aTideIsStartedForTheBranchWithADeployTask($branch, $cluster = null)
    {
        $this->aTideIsStartedWithTasks([
            [
                'deploy' => [
                    'cluster' => $cluster ?: 'fake/foo',
                    'services' => []
                ]
            ]
        ], $branch);
    }

    /**
     * @When a tide is started for the branch :branch with a build task
     */
    public function aTideIsStartedForTheBranchWithABuildTask($branch)
    {
        $this->aTideIsStartedWithTasks([
            [
                'build' => []
            ]
        ], $branch);
    }

    /**
     * @Given a tide is created with just a build task
     */
    public function aTideIsCreatedWithJustABuildTask()
    {
        $this->aTideIsCreatedWithConfiguration([
            'tasks' => [
                [
                    'build' => []
                ]
            ]
        ]);
    }

    /**
     * @Given a tide is created with a deploy task
     */
    public function aTideIsCreatedWithADeployTask()
    {
        $this->aTideIsCreatedWithConfiguration([
            'tasks' => [
                [
                    'deploy' => [
                        'cluster' => 'fake/foo',
                        'services' => [],
                    ]
                ]
            ]
        ]);
    }

    /**
     * @When a tide is started with a deploy task
     */
    public function aTideIsStartedWithADeployTask()
    {
        $this->aTideIsStartedWithTasks([
            [
                'deploy' => [
                    'cluster' => 'fake/foo',
                    'services' => [
                        'image0' => [
                            'specification' => [
                                'source' => [
                                    'image' => 'foo',
                                    'tag' => 'foo'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * @Given a tide is started with the following configuration:
     * @Given a tide is started for the branch :branch with the following configuration:
     */
    public function aTideIsStartedWithTheFollowingConfiguration(PyStringNode $configuration, $branch = null)
    {
        $this->aTideIsStartedWithConfiguration(Yaml::parse($configuration->getRaw()), $branch ?: 'master');
    }

    /**
     * @Given a tide is started with the following configurations:
     */
    public function aTideIsStartedWithTheFollowingConfigurations(TableNode $tasksTable)
    {
        $tasks = [];

        foreach ($tasksTable->getHash() as $task) {
            $configuration = !empty($task['configuration']) ? json_decode($task['configuration'], true) : [];

            if (!array_key_exists('name', $task)) {
                $task['name'] = count($tasks);
            }

            $taskConfiguration = [$task['type'] => $configuration];
            if (isset($task['filter']) && !empty($task['filter'])) {
                $taskConfiguration['filter'] = ['expression' => $task['filter']];
            }
            $tasks[$task['name']] = $taskConfiguration;
        }

        $this->aTideIsStartedWithTasks($tasks);
    }

    /**
     * @Then the configuration of the tide should contain at least:
     */
    public function theConfigurationOfTheTideShouldContainAtLeast(PyStringNode $string)
    {
        $tideCreatedEvents = $this->getEventsOfType(TideCreated::class);
        if (0 == count($tideCreatedEvents)) {
            throw new \RuntimeException('No tide created event found');
        }

        /** @var TideCreated $created */
        $created = current($tideCreatedEvents);
        $tideConfiguration = $created->getTideContext()->getConfiguration();

        $expectedConfiguration = Yaml::parse($string->getRaw());
        $intersection = $this->tideConfigurationContext->array_intersect_recursive($expectedConfiguration, $tideConfiguration);

        if ($intersection != $expectedConfiguration) {
            throw new \RuntimeException(sprintf(
                'Expected to have at least this configuration but found: %s',
                PHP_EOL.Yaml::dump($tideConfiguration)
            ));
        }
    }

    /**
     * @When I send a tide creation request for branch :branch and commit :sha1
     */
    public function iSendATideCreationRequestForBranchAndCommit($branch, $sha1)
    {
        $url = sprintf('/flows/%s/tides', (string) $this->flowContext->getCurrentUuid());

        $this->response = $this->kernel->handle(Request::create($url, 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'branch' => $branch,
            'sha1' => $sha1
        ])));
    }

    /**
     * @Then a tide should be created
     */
    public function aTideShouldBeCreated()
    {
        $this->assertResponseStatus(201);

        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        if (empty($json)) {
            throw new \RuntimeException('No tide was created');
        }

        $this->tideUuid = Uuid::fromString($json[0]['uuid']);
    }

    /**
     * @Then :count tides should have been created
     */
    public function tidesShouldHaveBeenCreated($count)
    {
        if (null !== $this->response) {
            $this->assertResponseStatus(201);

            $tides = \GuzzleHttp\json_decode($this->response->getContent(), true);
            if (count($tides) != $count) {
                throw new \RuntimeException(sprintf(
                    'Expected %d tides, but found %d',
                    $count,
                    count($tides)
                ));
            }
        } else if (count($this->getEventsOfType(TideCreated::class)) == 0) {
            throw new \RuntimeException('No response or no `TideCreated` event found');
        }
    }

    /**
     * @When I send a tide creation request for branch :branch
     */
    public function iSendATideCreationRequestForBranch($branch)
    {
        $url = sprintf('/flows/%s/tides', (string) $this->flowContext->getCurrentUuid());

        $this->response = $this->kernel->handle(Request::create($url, 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'branch' => $branch,
        ])));
    }

    /**
     * @Then a bad request error should be returned
     */
    public function aBadRequestErrorShouldBeReturned()
    {
        if ($this->response->getStatusCode() != 400) {
            throw new \RuntimeException(sprintf(
                'Expected status code 400, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then a permission error should be returned
     */
    public function aPermissionErrorShouldBeReturned()
    {
        if ($this->response->getStatusCode() != 403) {
            throw new \RuntimeException(sprintf(
                'Expected status code 403, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @When I send a tide creation request for commit :sha1
     */
    public function iSendATideCreationRequestForCommit($sha1)
    {
        $url = sprintf('/flows/%s/tides', (string) $this->flowContext->getCurrentUuid());

        $this->response = $this->kernel->handle(Request::create($url, 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'sha1' => $sha1
        ])));
    }

    /**
     * @Then the tide for the branch :branch and commit :sha1 should be started
     */
    public function theTideForTheCommitShouldBeStarted($branch, $sha1)
    {
        $tideStatus = $this->getTideByCodeReference($branch, $sha1)->getStatus();

        if ($tideStatus != Tide::STATUS_RUNNING) {
            throw new \RuntimeException(sprintf(
                'Expected status to be running but found "%s"',
                $tideStatus
            ));
        }
    }

    /**
     * @Then the tide for the branch :branch and commit :sha1 should not be started
     */
    public function theTideForTheCommitShouldNotBeStarted($branch, $sha1)
    {
        $tideStatus = $this->getTideByCodeReference($branch, $sha1)->getStatus();

        if ($tideStatus == Tide::STATUS_RUNNING) {
            throw new \RuntimeException(sprintf(
                'Expected status not to be running but found "%s"',
                $tideStatus
            ));
        }
    }

    /**
     * @Then the start of the pending tides of the branch :branch should be delayed
     */
    public function theStartOfThePendingTidesOfTheBranchShouldBeDelayed($branch)
    {
        $messages = $this->tracedMessageProducer->getProducedMessages();
        $matchingMessages = array_filter($messages, function($message) use ($branch) {
            if (!$message instanceof RunPendingTidesCommand) {
                return false;
            }

            return $message->getBranch() == $branch;
        });

        if (count($matchingMessages) == 0) {
            throw new \RuntimeException('No delayed message found');
        }
    }

    /**
     * @Then the environment deletion should be delayed
     */
    public function theEnvironmentDeletionShouldBePostponed()
    {
        $messages = $this->tracedMessageProducer->getProducedMessages();
        $matchingMessages = array_filter($messages, function($message) {
            return $message instanceof DeleteEnvironments;
        });

        if (count($matchingMessages) == 0) {
            throw new \RuntimeException('No delayed message found');
        }
    }

    /**
     * @Then the tide log archive command should be delayed
     */
    public function theTideLogArchiveCommandShouldBeDelayed()
    {
        $messages = $this->tracedMessageProducer->getProducedMessages();
        $matchingMessages = array_filter($messages, function($message) {
            return $message instanceof ArchiveTideCommand;
        });

        if (count($matchingMessages) == 0) {
            throw new \RuntimeException('No delayed message found');
        }
    }

    /**
     * @Given there is a pending tide created for branch :branch and commit :commit
     */
    public function thereIsATideCreatedForBranchAndCommit($branch, $commit)
    {
        $this->aTideIsCreatedForBranchAndCommitWithADeployTask($branch, $commit);
    }

    /**
     * @Given the tide :uuid is running and timed out
     */
    public function theTideIsRunningAndTimedOut($uuid)
    {
        $this->iHaveATide($uuid);

        $tideUuid = Uuid::fromString($uuid);
        $tide = $this->viewTideRepository->find($tideUuid);
        $tide->setStartDate((new \DateTime())->sub(new \DateInterval('P1D')));
        $tide->setStatus(Tide::STATUS_RUNNING);

        $this->viewTideRepository->save($tide);
    }

    /**
     * @Given there is a :status tide for the branch :branch
     * @Given there is a :status tide for the branch :branch and commit :commit
     */
    public function thereIsATideForTheBranch($status, $branch, $commit = null)
    {
        if ($status == 'failed') {
            $status = Tide::STATUS_FAILURE;
        } elseif ($status == 'successful') {
            $status = Tide::STATUS_SUCCESS;
        } else {
            throw new \RuntimeException(sprintf('Status "%s" unknown', $status));
        }

        $uuid = Uuid::uuid4()->toString();

        $this->iHaveATide($uuid, $branch, $commit);

        $tideUuid = Uuid::fromString($uuid);
        $tide = $this->viewTideRepository->find($tideUuid);
        $tide->setStatus($status);

        $this->viewTideRepository->save($tide);
    }

    /**
     * @Given the :branch branch in the repository for the flow :flow has the following tides:
     */
    public function theBranchInTheRepositoryForTheFlowHasTheFollowingTides($branch, $flow, TableNode $table)
    {
        foreach ($table->getHash() as $tide) {
            $uuid = Uuid::fromString($tide['tide']);
            $this->iHaveATide($uuid, $branch);
        }
    }

    /**
     * @Given I have a tide :uuid
     */
    public function iHaveATide($uuid, $branch = null, $commit = null)
    {
        $generationRequest = $this->createGenerationRequest($branch ?: 'master', $commit ?: sha1($branch ?: 'master'));

        $tide = $this->tideFactory->create(
            Pipeline::withConfiguration($generationRequest->getFlow(), [
                'tasks' => [],
                'name' => 'Default pipeline',
            ]),
            $generationRequest,
            Uuid::fromString($uuid)
        );

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }

    /**
     * @Then the tide :uuid should be failed
     */
    public function theTideShouldWithUuidBeFailed($uuid)
    {
        $tide = $this->viewTideRepository->find(Uuid::fromString($uuid));

        if ($tide->getStatus() != Tide::STATUS_FAILURE) {
            throw new \RuntimeException(sprintf(
                'Expected the tide to be failed but found "%s"',
                $tide->getStatus()
            ));
        }
    }

    /**
     * @Then the spot timed out tides command should be scheduled
     */
    public function theSpotTimedOutTidesCommandShouldBeScheduled()
    {
        $delayedCommands = $this->tracedMessageProducer->getProducedMessages();
        $matchingCommands = array_filter($delayedCommands, function($command) {
            return $command instanceof SpotTimedOutTidesCommand;
        });

        if (count($matchingCommands) == 0) {
            throw new \RuntimeException('No SpotTimedOutTidesCommand found');
        }
    }

    /**
     * @Then I should not see the tide :uuid
     */
    public function iShouldNotSeeTheTide($uuid)
    {
        $tide = $this->getTideFromResponse($uuid);

        if ($tide !== null) {
            throw new \RuntimeException(sprintf('Found tide %s', $uuid));
        }
    }

    /**
     * @Then I should see the tide :uuid
     */
    public function iShouldSeeTheTide($uuid)
    {
        $tide = $this->getTideFromResponse($uuid);

        if (null === $tide) {
            throw new \RuntimeException(sprintf('Tide %s not found', $uuid));
        }
    }

    /**
     * @When I should not see the configuration of the tide :tideUuid
     */
    public function iShouldNotSeeTheConfigurationOfTheTide($tideUuid)
    {
        if (null === ($tide = $this->getTideFromResponse($tideUuid))) {
            throw new \RuntimeException('Tide not found');
        }

        if (isset($tide['configuration'])) {
            throw new \RuntimeException('Got the tide configuration as well');
        }
    }

    /**
     * @Then I should be told that I don't have the permissions the list the tides
     * @Then I should be told that I don't have the permissions to cancel the tide
     */
    public function iShouldBeToldThatIDonTHaveThePermissionsTheListTheTides()
    {
        $this->assertResponseStatus(403);
    }

    /**
     * @Given there is the following tides:
     */
    public function thereIsTheFollowingTides(TableNode $table)
    {
        foreach ($table->getHash() as $tideRow) {
            $tide = Tide::create(
                Uuid::uuid4(),
                Uuid::fromString($tideRow['flow_uuid']),
                new CodeReference(new GitHubCodeRepository(1234, 'address', 'orga', 'name', false)),
                TreeLog::fromId('1234'),
                new Team('slug', 'name'),
                new User('username', Uuid::uuid4()),
                [],
                new \DateTime($tideRow['datetime'])
            );

            $tide->setStatus($tideRow['status']);

            $this->viewTideRepository->save($tide);
        }
    }

    /**
     * @param string $sha1
     *
     * @return Tide
     */
    private function getTideByCodeReference($branch, $sha1)
    {
        $flow = $this->flowContext->getCurrentFlow();
        $codeRepository = $flow->getCodeRepository();
        $tides = $this->viewTideRepository->findByCodeReference($flow->getUuid(), new CodeReference($codeRepository, $sha1, $branch));

        if (count($tides) != 1) {
            throw new \RuntimeException(sprintf(
                'Expected 1 tide but found %d for the commit %s',
                count($tides),
                $sha1
            ));
        }

        return current($tides);
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
     *
     * @return TideEvent[] array
     */
    public function getEventsOfType($eventType)
    {
        if (null === $this->tideUuid) {
            $this->tideUuid = $this->getTideUuid();
        }

        $events = $this->eventStore->findByTideUuid($this->tideUuid);

        return array_values(array_filter($events, function (TideEvent $event) use ($eventType) {
            return get_class($event) == $eventType || is_subclass_of($event, $eventType);
        }));
    }

    private function createTide($branch = 'master', $sha = null, Uuid $uuid = null)
    {
        $tide = $this->factoryTide($branch, $sha, $uuid);
        $this->tideUuid = $tide->getUuid();
    }

    /**
     * @param string $branch
     * @param null $sha
     * @param Uuid|null $uuid
     *
     * @return \ContinuousPipe\River\Tide
     */
    private function factoryTide($branch = 'master', $sha = null, Uuid $uuid = null)
    {
        $generationRequest = $this->createGenerationRequest($branch, $sha, $uuid);

        $this->commandBus->handle(new GenerateTides($generationRequest));
        $tides = $this->viewTideRepository->findByGenerationUuid(
            $generationRequest->getFlow()->getUuid(),
            $generationRequest->getGenerationUuid()
        );

        if (count($tides) != 1) {
            throw new \RuntimeException(sprintf(
                'Expected 1 tide, found %d',
                count($tides)
            ));
        }

        return $tides[0];
    }

    private function getTideFromResponse(string $tideUuid)
    {
        $this->assertResponseStatus(200);
        $tides = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingTides = array_filter($tides, function(array $tide) use ($tideUuid) {
            return $tide['uuid'] == $tideUuid;
        });

        if (count($matchingTides) == 0) {
            return null;
        }

        return current($matchingTides);
    }

    private function getTideUuid()
    {
        if (null === $this->tideUuid) {
            $flow = $this->flowContext->getCurrentFlow();
            $tides = $this->viewTideRepository->findLastByFlowUuid($flow->getUuid(), 1);

            if (count($tides) == 0) {
                throw new \RuntimeException(sprintf(
                    'Found no local tide UUID, and no tide in flow %s',
                    $flow->getUuid()
                ));
            }

            $this->tideUuid = $tides[0]->getUuid();
        }

        return $this->tideUuid;
    }

    /**
     * @param array $configuration
     * @param string $branch
     */
    private function aTideIsStartedWithConfiguration(array $configuration, $branch = 'master')
    {
        $this->aTideIsCreatedWithConfiguration($configuration, $branch);
        $this->startTide();
    }

    /**
     * @param array $configuration
     * @param string $branch
     */
    private function aTideIsCreatedWithConfiguration(array $configuration, $branch = 'master')
    {
        $continuousPipeFile = Yaml::dump($configuration);
        $this->flowContext->iHaveAFlow();
        $this->flowContext->getCodeRepositoryContext()->thereIsAFileContaining(
            'continuous-pipe.yml',
            $continuousPipeFile
        );

        $this->createTide($branch);
    }

    /**
     * @param int $index
     *
     * @return Tide
     */
    public function findTideByIndex($index)
    {
        $index = (int) $index;
        $tides = $this->viewTideRepository->findByFlowUuid(
            $this->flowContext->getCurrentUuid()
        );

        // Reverse the order because it's displayed from the last to the first
        $tides = array_reverse($tides->toArray());

        if (!array_key_exists($index, $tides)) {
            throw new \RuntimeException(sprintf('Tide #%d is not found', $index));
        }

        return $tides[$index];
    }

    private function assertDateEquals(\DateTime $expected, \DateTime $found = null)
    {
        if ($expected != $found) {
            throw new \RuntimeException(sprintf(
                'Expected %s but got %s',
                $expected->format(\DateTime::ISO8601),
                $found ? $found->format(\DateTime::ISO8601) : 'NULL'
            ));
        }
    }

    /**
     * @param int $status
     */
    private function assertResponseStatus($status)
    {
        if ($this->response->getStatusCode() != $status) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status %d but got %d',
                $status,
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @param string $branch
     * @param string $sha
     * @param UuidInterface $uuid
     *
     * @return TideGenerationRequest
     */
    private function createGenerationRequest($branch, $sha, UuidInterface $uuid = null): TideGenerationRequest
    {
        if (null === ($flow = $this->flowContext->getCurrentFlow())) {
            $flow = $this->flowContext->iHaveAFlow();
        }

        $sha = $sha ?: sha1(Random::string(8));
        $generation = Uuid::uuid4();
        $generationRequest = new TideGenerationRequest(
            $generation,
            Flow\Projections\FlatFlow::fromFlow($flow),
            new CodeReference(
                $flow->getCodeRepository(),
                $sha,
                $branch
            ),
            TideGenerationTrigger::user($flow->getUser()),
            $uuid
        );
        return $generationRequest;
    }

    public function getCurrentTide()
    {
        return $this->viewTideRepository->find(
            $this->tideUuid
        );
    }

    public function getCurrentTideAggregate()
    {
        return $this->tideRepository->find($this->tideUuid);
    }

    /**
     * @return \ContinuousPipe\River\Tide[]
     */
    public function getCreatedTides()
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        return array_map(function(array $tide) {
            return $this->tideRepository->find(Uuid::fromString($tide['uuid']));
        }, $json);
    }

    /**
     * @When I create a tide :tide for the flow :flow for branch :branch and commit :commit
     */
    public function iCreateATideForBranchAndCommit($tide, $flow, $branch, $commit)
    {
        $this->createTide($branch, $commit, Uuid::fromString($tide));
    }
}
