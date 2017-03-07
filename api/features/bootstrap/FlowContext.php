<?php

use AppBundle\Command\Config\GenerateDocumentationCommand;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use ContinuousPipe\River\EventStore\EventStore;
use ContinuousPipe\River\Infrastructure\Firebase\Pipeline\View\Storage\InMemoryPipelineViewStorage;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Tests\Pipe\FakeClient;
use ContinuousPipe\River\Tests\Pipe\TraceableClient;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\FlowContext as RiverFlowContext;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use GitHub\WebHook\Model\Repository;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;
use Symfony\Component\Yaml\Yaml;

class FlowContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var \SecurityContext
     */
    private $securityContext;

    /**
     * @var InitializedContextEnvironment
     */
    private $environment;

    /**
     * @var string
     */
    private $flowUuid;

    /**
     * @var Flow
     */
    private $currentFlow;

    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var InMemoryCodeRepositoryRepository
     */
    private $codeRepositoryRepository;

    /**
     * @var InMemoryAuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @var FakeClient
     */
    private $pipeClient;

    /**
     * @var TraceableClient
     */
    private $traceablePipeClient;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var string|null
     */
    private $lastConfiguration;
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    private $flowConfiguration;

    /**
     * @var string
     */
    private $output;

    public function __construct(
        Kernel $kernel,
        FlowRepository $flowRepository,
        InMemoryCodeRepositoryRepository $codeRepositoryRepository,
        InMemoryAuthenticatorClient $authenticatorClient,
        FakeClient $pipeClient,
        TraceableClient $traceablePipeClient,
        TeamRepository $teamRepository,
        MessageBus $eventBus
    ) {
        $this->flowRepository = $flowRepository;
        $this->kernel = $kernel;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
        $this->authenticatorClient = $authenticatorClient;
        $this->pipeClient = $pipeClient;
        $this->teamRepository = $teamRepository;
        $this->traceablePipeClient = $traceablePipeClient;
        $this->eventBus = $eventBus;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->environment = $scope->getEnvironment();
        $this->securityContext = $this->environment->getContext('SecurityContext');
    }

    /**
     * @return Uuid
     */
    public function getCurrentUuid()
    {
        return is_string($this->flowUuid) ? Uuid::fromString($this->flowUuid) : $this->flowUuid;
    }

    /**
     * @When I load the alerts of the flow :uuid
     */
    public function iLoadTheAlertsOfTheFlow($uuid)
    {
        $this->response = $this->kernel->handle(Request::create('/flows/'.$uuid.'/alerts'));

        $this->assertResponseCode(200);
    }

    /**
     * @Then the flow UUID should be :uuid
     */
    public function theFlowUuidShouldBe($uuid)
    {
        if ($this->flowUuid != $uuid) {
            throw new \RuntimeException(sprintf(
                'Found UUID %s but expected %s',
                $this->flowUuid,
                $uuid
            ));
        }
    }

    /**
     * @When I delete the flow :flowUuid
     */
    public function iDeleteTheFlow($flowUuid)
    {
        $this->response = $this->kernel->handle(Request::create('/flows/'.$flowUuid, 'DELETE'));
    }

    /**
     * @Then the flow should be successfully deleted
     */
    public function theFlowShouldBeSuccessfullyDeleted()
    {
        $this->assertResponseCode(204);
    }

    /**
     * @When I request the flow configuration
     */
    public function iRequestTheFlowConfiguration()
    {
        $this->response = $this->kernel->handle(Request::create('/flows/'.$this->flowUuid.'/configuration'));

        $this->assertResponseCode(200);
    }

    /**
     * @When I request the flow
     * @When I request the flow with UUID :uuid
     */
    public function iRequestTheFlowWithUuid($uuid = null)
    {
        if (null === $uuid) {
            $uuid = (string) $this->getCurrentUuid();
        }
        
        $this->response = $this->kernel->handle(Request::create('/flows/'.$uuid));

        $this->assertResponseCode(200);
    }

    /**
     * @When I update the flow to the version :version and the following configuration:
     */
    public function iUpdateTheFlowToTheVersionAndTheFollowingConfiguration($version, PyStringNode $string)
    {
        $this->response = $this->kernel->handle(Request::create('/flows/'.$this->flowUuid.'/configuration', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'version' => (int) $version,
            'yaml' => $string->getRaw()
        ])));
    }

    /**
     * @When I send a flow creation request for the team :teamUuid with the :repositoryType repository :repositoryIdentifier
     * @When I send a flow creation request for the team :teamUuid with the :repositoryType repository :repositoryIdentifier and the UUID :uuid
     */
    public function iSendAFlowCreationRequestForTheTeamWithTheGithubRepositoryAndTheUuid($teamUuid, $repositoryType, $repositoryIdentifier, $uuid = null)
    {
        $this->response = $this->kernel->handle(Request::create('/teams/'.$teamUuid.'/flows', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'repository' => [
                'type' => strtolower($repositoryType),
                'identifier' => $repositoryIdentifier,
                'organisation' => 'sroze',
                'name' => 'php-example',
            ],
            'uuid' => $uuid,
        ])));

        $flowView = json_decode($this->response->getContent(), true);
        if (array_key_exists('uuid', $flowView)) {
            $this->flowUuid = $flowView['uuid'];
        }
    }

    /**
     * @When I send a deprecated flow creation request with the following parameters:
     */
    public function iSendADeprecatedFlowCreationRequestWithTheFollowingParameters(TableNode $parameters)
    {
        $creationRequest = json_encode($parameters->getHash()[0]);

        $this->response = $this->kernel->handle(Request::create('/flows', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $creationRequest));

        $flowView = json_decode($this->response->getContent(), true);
        if (array_key_exists('uuid', $flowView)) {
            $this->flowUuid = $flowView['uuid'];
        }
    }

    /**
     * @When I send an update request with a configuration
     */
    public function iSendAnUpdateRequestWithAValidConfiguration()
    {
        $this->lastConfiguration = <<<EOF
tasks:
    - build: ~
    - deploy:
        cluster: foo
EOF;

        $url = sprintf('/flows/%s/configuration', $this->flowUuid);
        $this->response = $this->kernel->handle(Request::create($url, 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'yml_configuration' => $this->lastConfiguration
        ])));
    }

    /**
     * @Given the flow :flowUuid have the following configuration:
     * @When I send an update request with the following configuration:
     */
    public function iSendAnUpdateRequestWithTheFollowingConfiguration(PyStringNode $string, $flowUuid = null)
    {
        $url = sprintf('/flows/%s/configuration', $flowUuid ?: $this->flowUuid);
        $this->response = $this->kernel->handle(Request::create($url, 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'yml_configuration' => $string->getRaw()
        ])));
    }

    /**
     * @When I send a pipeline deletion request for flow :uuid and pipeline :pipelineName
     */
    public function iSendAPipelineDeletionRequestForFlowAndPipeline($uuid, $pipelineName)
    {
        $flow = $this->flowRepository->find(Uuid::fromString($uuid));
        $flatFlow = Flow\Projections\FlatFlow::fromFlow($flow);
        $pipeline = Pipeline::withConfiguration($flatFlow, ['name' => $pipelineName]);

        $url = sprintf('/flows/%s/pipeline/%s', $uuid, $pipeline->getUuid());
        $this->response = $this->kernel->handle(Request::create($url, 'DELETE', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]));
    }

    /**
     * @When I retrieve the list of the flows
     */
    public function iRetrieveTheListOfTheFlows()
    {
        $this->response = $this->kernel->handle(Request::create('/teams/samuel/flows', 'GET'));
    }

    /**
     * @When I retrieve the list of the flows of the team :teamSlug
     */
    public function iRetrieveTheListOfTheFlowsOfTheTeam($teamSlug)
    {
        $this->response = $this->kernel->handle(Request::create(sprintf('/teams/%s/flows', $teamSlug), 'GET'));
    }

    /**
     * @When I retrieve the list of the flows of the team :teamSlug with the API key :apiKey
     */
    public function iRetrieveTheListOfTheFlowsOfTheTeamWithTheApiKey($teamSlug, $apiKey)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/teams/%s/flows', $teamSlug),
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $apiKey
            ]
        ));
    }

    /**
     * @Then the missing variables should be a sequential array
     */
    public function theMissingVariablesShouldBeASequentialArray()
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $missingVariables = $json['missing_variables'];

        if (array_keys($missingVariables) !== range(0, count($missingVariables) - 1)) {
            throw new \RuntimeException(sprintf(
                'Found the following keys for the array: %s',
                implode(',', array_keys($missingVariables))
            ));
        }
    }

    /**
     * @Then the variable :variable should be missing
     */
    public function theVariableShouldBeMissing($variable)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if (!in_array($variable, $json['missing_variables'])) {
            throw new \RuntimeException('The variable is not found is the missing variables');
        }
    }

    /**
     * @Then the variable :variable should not be missing
     */
    public function theVariableShouldNotBeMissing($variable)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if (in_array($variable, $json['missing_variables'])) {
            throw new \RuntimeException('The variable is found is the missing variables');
        }
    }

    /**
     * @Then I should see the flow :uuid
     */
    public function iShouldSeeTheFlow($uuid)
    {
        $this->assertResponseCode(200);

        $flows = json_decode($this->response->getContent(), true);
        if (!is_array($flows)) {
            throw new \RuntimeException('Expected to receive an array');
        }

        $matchingFlows = array_filter($flows, function(array $flow) use ($uuid) {
            return $flow['uuid'] = $uuid;
        });

        if (0 == count($matchingFlows)) {
            throw new \RuntimeException('No matching flow found');
        }
    }

    /**
     * @Then I should see the pipeline :name in the flow
     */
    public function iShouldSeeThePipelineInTheFlow($name)
    {
        $flow = json_decode($this->response->getContent(), true);
        if (!is_array($flow)) {
            throw new \RuntimeException('Expected to receive an array');
        }

        $matchingPipelines = array_filter($flow['pipelines'], function(array $pipeline) use ($name) {
            return $pipeline['name'] == $name;
        });

        if (count($matchingPipelines) == 0) {
            throw new \RuntimeException('No matching pipeline found');
        }
    }

    /**
     * @Then I should not see the pipeline :name in the flow
     */
    public function iShouldNotSeeThePipelineInTheFlow($name)
    {
        $flow = json_decode($this->response->getContent(), true);
        if (!is_array($flow)) {
            throw new \RuntimeException('Expected to receive an array');
        }

        $matchingPipelines = array_filter($flow['pipelines'], function(array $pipeline) use ($name) {
            return $pipeline['name'] == $name;
        });

        if (count($matchingPipelines) > 0) {
            throw new \UnexpectedValueException('Found a matching pipeline, but not expected.');
        }
    }

    /**
     * @Then I should see the flow's last tide
     */
    public function iShouldSeeTheFlowSLastTide()
    {
        $this->assertResponseCode(200);

        $flows = json_decode($this->response->getContent(), true);
        if (!is_array($flows)) {
            throw new \RuntimeException('Expected to receive an array');
        }

        $matchingFlows = array_filter($flows, function(array $flow) {
            return isset($flow['tides']) && !empty($flow['tides']);
        });

        if (0 == count($matchingFlows)) {
            throw new \RuntimeException('No matching flow found');
        }
    }

    /**
     * @Then the flow is not saved
     * @Then I should be told that my flow creation request is invalid
     * @Then the flow is not saved because of a bad request error
     */
    public function theFlowIsNotSaved()
    {
        $this->assertResponseCode(400);
    }

    /**
     * @Then the flow is not saved because of an authorization exception
     */
    public function theFlowIsNotSavedBecauseOfAnAuthorizationException()
    {
        $this->assertResponseCode(403);
    }

    /**
     * @Then the flow is successfully saved
     */
    public function theFlowIsSuccessfullySaved()
    {
        $this->assertResponseCode(200);
    }

    /**
     * @Then the pipeline is successfully removed
     */
    public function successfullyRemoved()
    {
        $this->assertResponseCode(204);
    }

    /**
     * @Then the stored configuration is not empty
     */
    public function theStoredConfigurationIsNotEmpty()
    {
        $flow = $this->flowRepository->find(Uuid::fromString($this->flowUuid));
        $configuration = $flow->getConfiguration();

        if (empty($configuration)) {
            throw new \RuntimeException('Found empty configuration while expecting it to be saved');
        }
    }

    /**
     * @Given I have a flow
     */
    public function iHaveAFlow()
    {
        if (null === $this->currentFlow) {
            $this->createFlow();
        }

        return $this->currentFlow;
    }

    /**
     * @Given I have a flow with a BitBucket repository :name owned by user :username
     * @Given I have a flow :uuid with a BitBucket repository :name owned by user :username
     */
    public function iHaveAFlowWithABitBucketRepositoryOwnerByUser($name, $username, $uuid = null)
    {
        $this->createFlow(
            $uuid !== null ? Uuid::fromString($uuid) : null,
            [], null, new CodeRepository\BitBucket\BitBucketCodeRepository(
            Uuid::uuid5(Uuid::NIL, $name)->toString(),
            new CodeRepository\BitBucket\BitBucketAccount(
                '{UUID}',
                $username,
                'user'
            ),
            $name,
            'https://api.bitbucket.org/2.0/repositories/'.$username.'/'.$name,
            'master',
            true
        ));
    }

    /**
     * @Given I have a flow :uuid with a GitHub repository :repository owned by :owner
     */
    public function iHaveAFlowWithAGithubRepositoryOwnedBy($uuid, $repository, $owner)
    {
        $this->createFlow(
            $uuid !== null ? Uuid::fromString($uuid) : null,
            [], null, new CodeRepository\GitHub\GitHubCodeRepository(
            Uuid::uuid5(Uuid::NIL, $repository)->toString(),
            'https://api.github.com/repos/'.$owner.'/'.$repository,
            $owner,
            $repository,
            true,
            'master'
        ));
    }

    /**
     * @Given I have a flow with UUID :uuid
     * @Given there is a flow with UUID :uuid
     */
    public function iHaveAFlowWithUuid($uuid)
    {
        if (null === $this->currentFlow) {
            $this->createFlow(Uuid::fromString($uuid));
        }
    }

    /**
     * @Given I have a flow in the team :teamSlug
     */
    public function iHaveAFlowInTheTeam($teamSlug)
    {
        $team = $this->teamRepository->find($teamSlug);

        $this->createFlow(Uuid::uuid1(), [], $team);
    }

    /**
     * @Given I have a flow with UUID :uuid in the team :teamSlug
     */
    public function iHaveAFlowWithUuidInTheTeam($uuid, $teamSlug)
    {
        $team = $this->teamRepository->find($teamSlug);

        $this->createFlow(Uuid::fromString($uuid), [], $team);
    }

    /**
     * @Given I have a flow with the following configuration:
     * @Given I have a flow with UUID :uuid and the following configuration:
     */
    public function iHaveAFlowWithTheFollowingConfiguration(PyStringNode $string, $uuid = null)
    {
        if (null === $this->currentFlow || $uuid !== null) {
            $this->createFlow($uuid !== null ? Uuid::fromString($uuid) : null, Yaml::parse($string->getRaw()));
        }
    }

    /**
     * @Given I have a deployed environment named :name on the cluster :cluster
     */
    public function iHaveADeployedEnvironmentNamed($name, $cluster)
    {
        $this->pipeClient->addEnvironment($cluster, new Environment($name, $name));
    }

    /**
     * @Given I have a deployed environment named :name and labelled :labelsString on the cluster :cluster
     */
    public function iHaveADeployedEnvironmentNamedAndLabelled($name, $labelsString, $cluster)
    {
        $labels = [];
        foreach (explode(',', $labelsString) as $label) {
            list($key, $value) = explode('=', $label);

            $labels[$key] = $value;
        }

        $this->pipeClient->addEnvironment($cluster, new Environment($name, $name, [], null, $labels));
    }

    /**
     * @When I request the list of deployed environments of the flow :uuid
     */
    public function iRequestTheListOfDeployedEnvironmentsOfTheFlow($uuid)
    {
        $url = sprintf('/flows/%s/environments', $uuid);
        $this->response = $this->kernel->handle(Request::create($url, 'GET'));

        $this->assertResponseCode(200);
    }

    /**
     * @When I delete the environment named :name deployed on :cluster of the flow :uuid
     */
    public function iDeleteTheEnvironmentNamedOfTheFlow($name, $cluster, $uuid)
    {
        $this->iTentativelyDeleteTheEnvironmentNamedOfTheFlow($name, $cluster, $uuid);
        $this->assertResponseCode(204);
    }

    /**
     * @When I should be told that I don't have the permissions
     */
    public function iShouldBeToldThatIDonTHaveThePermissions()
    {
        $this->assertResponseCode(403);
    }

    /**
     * @Then I should be told that I am not authenticated
     */
    public function iShouldBeToldThatIamNotAuthenticated()
    {
        $this->assertResponseCode(401);
    }

    /**
     * @When I tentatively delete the environment named :name deployed on :cluster of the flow :uuid
     */
    public function iTentativelyDeleteTheEnvironmentNamedOfTheFlow($name, $cluster, $uuid)
    {
        $url = sprintf('/flows/%s/environments/%s?cluster=%s', $uuid, $name, $cluster);
        $this->response = $this->kernel->handle(Request::create($url, 'DELETE'));
    }

    /**
     * @Then the environment :name should have been deleted
     */
    public function theEnvironmentShouldHaveBeenDeleted($name)
    {
        $deletedEnvironments = array_map(function(Target $target) {
            return $target->getEnvironmentName();
        }, $this->traceablePipeClient->getDeletions());

        if (!in_array($name, $deletedEnvironments)) {
            throw new \RuntimeException(sprintf('Environment not found in (%s)', implode(', ', $deletedEnvironments)));
        }
    }

    /**
     * @Then I should see the environment :name
     */
    public function iShouldSeeTheEnvironment($name)
    {
        $environments = json_decode($this->response->getContent(), true);
        $matchingEnvironments = array_filter($environments, function(array $environment) use ($name) {
            return $environment['identifier'] == $name;
        });

        if (0 == count($matchingEnvironments)) {
            throw new \RuntimeException(sprintf(
                'No environment named "%s" found',
                $name
            ));
        }
    }

    /**
     * @Then I should receive an empty list of environments
     */
    public function iShouldReceiveAnEmptyListOfEnvironments()
    {
        $environments = json_decode($this->response->getContent(), true);

        if (!is_array($environments)) {
            throw new \RuntimeException('The response does not look like it is a JSON array');
        }

        if (count($environments) > 0) {
            throw new \RuntimeException(sprintf(
                'Expected to have 0 environments, found %d',
                count($environments)
            ));
        }
    }

    /**
     * @Then I should not see the environment :name
     */
    public function iShouldNotSeeTheEnvironment($name)
    {
        $environments = json_decode($this->response->getContent(), true);
        $matchingEnvironments = array_filter($environments, function(array $environment) use ($name) {
            return $environment['identifier'] == $name;
        });

        if (0 != count($matchingEnvironments)) {
            throw new \RuntimeException(sprintf(
                'Environment "%s" found',
                $name
            ));
        }
    }

    /**
     * @Then I should see the :type alert
     */
    public function iShouldSeeTheAlert($type)
    {
        $alerts = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingAlerts = array_filter($alerts, function($alert) use ($type) {
            return $alert['type'] == $type;
        });

        if (count($matchingAlerts) == 0) {
            throw new \RuntimeException('No matching alert found');
        }
    }

    /**
     * @Then I should not see the :type alert
     */
    public function iShouldNotSeeTheAlert($type)
    {
        $alerts = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingAlerts = array_filter($alerts, function($alert) use ($type) {
            return $alert['type'] == $type;
        });

        if (count($matchingAlerts) != 0) {
            throw new \RuntimeException('Matching alert found');
        }
    }

    /**
     * @When I request the :uuid account's personal repositories
     */
    public function iRequestTheAccountSPersonalRepositories($uuid)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/account/'.$uuid.'/repositories'
        ));

        $this->assertResponseCode(200);
    }

    /**
     * @When I request the :uuid account's organisations
     */
    public function iRequestTheAccountSOrganisations($uuid)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/account/'.$uuid.'/organisations'
        ));

        $this->assertResponseCode(200);
    }

    /**
     * @Then I should see the following organisations:
     */
    public function iShouldSeeTheFollowingOrganisations(TableNode $table)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        foreach ($table->getHash() as $row) {
            if (!$this->responseHasRow($json, $row)) {
                throw new \RuntimeException('The response do not contain the organisation');
            }
        }
    }

    /**
     * @When I request the :uuid account's repositories of the organisation :organisation
     */
    public function iRequestTheAccountSRepositoriesOfTheOrganisation($uuid, $organisation)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/account/'.$uuid.'/organisations/'.$organisation.'/repositories'
        ));

        $this->assertResponseCode(200);
    }

    /**
     * @Then I should see the following repositories:
     */
    public function iShouldSeeTheFollowingRepositories(TableNode $table)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        foreach ($table->getHash() as $row) {
            if (!$this->responseHasRow($json, $row)) {
                throw new \RuntimeException(sprintf('The response do not contain the "%s" repository', $row['name']));
            }
        }
    }

    /**
     * @When I request the encrypted value of :plainValue for the flow :flowUuid
     */
    public function iRequestTheEncryptedValueOfForTheFlow($plainValue, $flowUuid)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/flows/'.$flowUuid.'/encrypt-variable',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'plain' => $plainValue
            ])
        ));
    }

    /**
     * @Then I should receive the encrypted value :encryptedValue
     */
    public function iShouldReceiveTheEncryptedValue($encryptedValue)
    {
        $this->assertResponseCode(200);

        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        if ($json['encrypted'] != $encryptedValue) {
            throw new \RuntimeException(sprintf(
                'Got the encrypted value "%s" instead',
                $json['encrypted']
            ));
        }
    }

    /**
     * @Then the encryption should be forbidden
     */
    public function theEncryptionShouldBeForbidden()
    {
        $this->assertResponseCode(403);
    }

    /**
     * @param Uuid $uuid
     * @param array $configuration
     * @param Team $team
     * @param CodeRepository $codeRepository
     *
     * @return Flow
     */
    public function createFlow(Uuid $uuid = null, array $configuration = [], Team $team = null, CodeRepository $codeRepository = null)
    {
        $uuid = $uuid ?: Uuid::uuid1();
        $team = $team ?: $this->securityContext->theTeamExists('samuel');
        $user = new User('samuel.roze@gmail.com', Uuid::uuid1());
        $repository = $codeRepository ?: $this->generateRepository();

        array_map(function($event) use ($uuid) {
            $this->eventBus->handle($event);
        }, [
            new Flow\Event\FlowCreated(
                $uuid,
                $team,
                $user,
                $repository
            ),
            new Flow\Event\FlowConfigurationUpdated(
                $uuid,
                $configuration
            )
        ]);

        $this->codeRepositoryRepository->add($repository);
        $this->authenticatorClient->addUser($user);

        $this->flowUuid = (string) $uuid;
        $this->currentFlow = $flow = $this->flowRepository->find($uuid);

        return $flow;
    }

    /**
     * @Then the environment should be deleted
     */
    public function theEnvironmentShouldBeDeleted()
    {
        $deletions = $this->traceablePipeClient->getDeletions();

        if (0 == count($deletions)) {
            throw new \RuntimeException('No deleted environment found');
        }
    }

    /**
     * @Then the environment should not be deleted
     */
    public function theEnvironmentShouldNotBeDeleted()
    {
        $deletions = $this->traceablePipeClient->getDeletions();

        if (0 != count($deletions)) {
            throw new \RuntimeException('Deleted environment(s) found');
        }
    }

    /**
     * @return Flow
     */
    public function getCurrentFlow()
    {
        return $this->currentFlow;
    }


    /**
     * @Given the configuration schema is defined
     */
    public function theConfigurationSchemaIsDefined()
    {
        $this->flowConfiguration = new Flow\TestConfiguration();
    }

    /**
     * @When I run the documentation generator console command
     */
    public function iRunTheDocumentationGeneratorConsoleCommand()
    {
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $command = new GenerateDocumentationCommand('test:config:generate', $this->flowConfiguration);
        $command->run($input, $output);

        $this->output = $output->fetch();
    }

    /**
     * @Then I should see the following output:
     */
    public function iShouldSeeTheFollowingOutput(PyStringNode $string)
    {
        if ($string->getRaw() !== $this->output) {
            $diff = new Diff($string->getStrings(), explode("\n", $this->output));
            $renderer = new Diff_Renderer_Text_Unified();
            throw new \UnexpectedValueException($diff->render($renderer));
        }
    }

    /**
     * @param int $code
     */
    private function assertResponseCode($code)
    {
        if ($this->response->getStatusCode() != $code) {
            echo $this->response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected response code %d, but got %d',
                $code,
                $this->response->getStatusCode()
            ));
        }
    }

    private function responseHasRow(array $json, array $row): bool
    {
        foreach ($json as $repository) {
            $matching = true;
            foreach ($row as $key => $value) {
                if ($repository[$key] !== $value) {
                    $matching = false;
                }
            }

            if ($matching) {
                return $matching;
            }
        }

        return false;
    }

    private function generateRepository()
    {
        return $this->getCodeRepositoryContext()->thereIsARepositoryIdentified();
    }

    public function getCodeRepositoryContext() : CodeRepositoryContext
    {
        if ($this->environment->hasContextClass(GitHubContext::class)) {
            $context = $this->environment->getContext(GitHubContext::class);
        } elseif ($this->environment->hasContextClass(BitBucketContext::class)) {
            $context = $this->environment->getContext(BitBucketContext::class);
        } else {
            throw new \RuntimeException('Unable to find the code repository context');
        }

        if (!$context instanceof CodeRepositoryContext) {
            throw new \RuntimeException('The code repository context must implement the '.CodeRepositoryContext::class.' interface');
        }

        return $context;
    }
}
