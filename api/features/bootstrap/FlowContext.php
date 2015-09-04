<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\Tests\Pipe\FakeClient;
use Rhumsaa\Uuid\Uuid;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\FlowContext as RiverFlowContext;
use ContinuousPipe\User\User;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use GitHub\WebHook\Model\Repository;
use ContinuousPipe\User\Tests\Authenticator\InMemoryAuthenticatorClient;

class FlowContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var Uuid
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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

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
     * @var Response|null
     */
    private $response;

    /**
     * @param Kernel $kernel
     * @param TokenStorageInterface $tokenStorage
     * @param FlowRepository $flowRepository
     * @param InMemoryCodeRepositoryRepository $codeRepositoryRepository
     * @param InMemoryAuthenticatorClient $authenticatorClient
     * @param FakeClient $pipeClient
     */
    public function __construct(Kernel $kernel, TokenStorageInterface $tokenStorage, FlowRepository $flowRepository, InMemoryCodeRepositoryRepository $codeRepositoryRepository, InMemoryAuthenticatorClient $authenticatorClient, FakeClient $pipeClient)
    {
        $this->flowRepository = $flowRepository;
        $this->kernel = $kernel;
        $this->tokenStorage = $tokenStorage;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
        $this->authenticatorClient = $authenticatorClient;
        $this->pipeClient = $pipeClient;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new \ContinuousPipe\User\SecurityUser(new \ContinuousPipe\User\User('samuel')));
        $this->tokenStorage->setToken($token);
    }

    /**
     * @return Uuid
     */
    public function getCurrentUuid()
    {
        return $this->flowUuid;
    }

    /**
     * @When I send a flow creation request
     */
    public function iSendAFlowCreationRequest()
    {
        $this->codeRepositoryRepository->add(new CodeRepository\GitHub\GitHubCodeRepository(
            new Repository('foo', 'bar', false, '1234')
        ));

        $creationRequest = <<<EOF
{
   "repository": "1234",
   "tasks": [
      {"name": "build"},
      {"name": "deploy"}
   ]
}
EOF;

        $response = $this->kernel->handle(Request::create('/flows', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $creationRequest));

        $flowView = json_decode($response->getContent(), true);
        $this->flowUuid = $flowView['uuid'];
    }

    /**
     * @Then the flow is successfully saved
     */
    public function theFlowIsSuccessfullySaved()
    {
        $this->flowRepository->find(Uuid::fromString($this->flowUuid));
    }

    /**
     * @Given I have a flow with the build task
     */
    public function iHaveAFlowWithTheBuildTask()
    {
        $this->createFlowWithTasks([
            new Flow\Task('build'),
        ]);
    }

    /**
     * @Given I have a flow with a deploy task
     */
    public function iHaveAFlowWithADeployTask()
    {
        $this->createFlowWithTasks([
            new Flow\Task('deploy', [
                'providerName' => 'fake/provider',
            ]),
        ]);
    }

    /**
     * @Given I have a flow
     * @Given I have a flow with the build and deploy tasks
     */
    public function iHaveAFlowWithTheBuildAndDeployTasks()
    {
        $this->createFlow();
    }

    /**
     * @Given I have a flow with UUID :uuid
     */
    public function iHaveAFlowWithUuid($uuid)
    {
        $this->createFlow(Uuid::fromString($uuid));
    }

    /**
     * @Given I have the a deployed environment named :name
     */
    public function iHaveTheADeployedEnvironmentNamed($name)
    {
        $this->pipeClient->addEnvironment(new Environment($name, $name));
    }

    /**
     * @When I request the list of deployed environments
     */
    public function iRequestTheListOfDeployedEnvironments()
    {
        $url = sprintf('/flows/%s/environments', (string) $this->flowUuid);
        $this->response = $this->kernel->handle(Request::create($url, 'GET'));

        if ($this->response->getStatusCode() != 200) {
            throw new \RuntimeException(sprintf(
                'Expected response code 200, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then I should see the environment :name
     */
    public function iShouldSeeTheEnvironment($name)
    {
        $environments = json_decode($this->response->getContent(), true);
        $matchingEnvironments = array_filter($environments, function(array $environment) use ($name) {
            return $environment['name'] == $name;
        });

        if (0 == count($matchingEnvironments)) {
            throw new \RuntimeException(sprintf(
                'No environment named "%s" found',
                $name
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
            return $environment['name'] == $name;
        });

        if (0 != count($matchingEnvironments)) {
            throw new \RuntimeException(sprintf(
                'Environment named "%s" found',
                $name
            ));
        }
    }

    /**
     * @return Flow
     */
    public function createFlow(Uuid $uuid = null)
    {
        $context = $this->createFlowContext($uuid);

        return $this->createFlowWithContextAndTasks($context, [
            new Flow\Task('build'),
            new Flow\Task('deploy', [
                'providerName' => 'fake/provider',
            ]),
        ]);
    }

    /**
     * @param Flow\Task[] $tasks
     */
    public function createFlowWithTasks(array $tasks)
    {
        $context = $this->createFlowContext();

        $this->createFlowWithContextAndTasks($context, $tasks);
    }

    /**
     * @param CodeRepository $codeRepository
     *
     * @return RiverFlowContext
     */
    private function createFlowContextWithCodeRepository(CodeRepository $codeRepository, Uuid $uuid = null)
    {
        $this->flowUuid = $uuid ?: Uuid::uuid1();
        $user = new User('samuel.roze@gmail.com');

        $this->codeRepositoryRepository->add($codeRepository);
        $this->authenticatorClient->addUser($user);

        return RiverFlowContext::createFlow(
            $this->flowUuid,
            $user,
            $codeRepository
        );
    }

    /**
     * @param RiverFlowContext $context
     * @param Flow\Task[]      $tasks
     *
     * @return Flow
     */
    public function createFlowWithContextAndTasks(RiverFlowContext $context, array $tasks)
    {
        $flow = new Flow($context, $tasks);
        $this->flowRepository->save($flow);

        $this->currentFlow = $flow;

        return $flow;
    }

    /**
     * @return Flow
     */
    public function getCurrentFlow()
    {
        return $this->currentFlow;
    }

    /**
     * @return RiverFlowContext
     */
    private function createFlowContext(Uuid $uuid = null)
    {
        return $this->createFlowContextWithCodeRepository(new CodeRepository\GitHub\GitHubCodeRepository(
            new Repository('foo', 'bar')
        ), $uuid);
    }
}
