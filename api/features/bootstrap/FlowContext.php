<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\Tests\Pipe\FakeClient;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Rhumsaa\Uuid\Uuid;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\FlowContext as RiverFlowContext;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use GitHub\WebHook\Model\Repository;
use Symfony\Component\Yaml\Yaml;

class FlowContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
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
     * @var string|null
     */
    private $lastConfiguration;

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
        $token->setUser(new SecurityUser(new User('samuel.roze@gmail.com', Uuid::uuid1())));
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
     * @When I send a flow creation request with the UUID :uuid
     */
    public function iSendAFlowCreationRequestWithTheUuid($uuid)
    {
        $this->codeRepositoryRepository->add(new CodeRepository\GitHub\GitHubCodeRepository(
            new Repository('foo', 'bar', false, '1234')
        ));

        $creationRequest = <<<EOF
{
   "repository": "1234",
   "uuid": "$uuid"
}
EOF;

        $this->response = $this->kernel->handle(Request::create('/flows', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $creationRequest));

        $flowView = json_decode($this->response->getContent(), true);
        $this->flowUuid = $flowView['uuid'];
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
     * @When I send a flow creation request
     */
    public function iSendAFlowCreationRequest()
    {
        $this->codeRepositoryRepository->add(new CodeRepository\GitHub\GitHubCodeRepository(
            new Repository('foo', 'bar', false, '1234')
        ));

        $creationRequest = <<<EOF
{
   "repository": "1234"
}
EOF;

        $this->response = $this->kernel->handle(Request::create('/flows', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $creationRequest));

        $flowView = json_decode($this->response->getContent(), true);
        $this->flowUuid = $flowView['uuid'];
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
        providerName: foo
EOF;

        $url = sprintf('/flows/%s', $this->flowUuid);
        $this->response = $this->kernel->handle(Request::create($url, 'PUT', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'yml_configuration' => $this->lastConfiguration
        ])));
    }


    /**
     * @When I retrieve the list of the flows
     */
    public function iRetrieveTheListOfTheFlows()
    {
        $this->response = $this->kernel->handle(Request::create('/flows', 'GET'));
    }

    /**
     * @Then I should see the flow :uuid
     */
    public function iShouldSeeTheFlow($uuid)
    {
        if ($this->response->getStatusCode() != 200) {
            throw new \RuntimeException(sprintf(
                'The status code 200 was expected, found %d',
                $this->response->getStatusCode()
            ));
        }

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
     * @Then I should see the flow's last tide
     */
    public function iShouldSeeTheFlowSLastTide()
    {
        if ($this->response->getStatusCode() != 200) {
            throw new \RuntimeException(sprintf(
                'The status code 200 was expected, found %d',
                $this->response->getStatusCode()
            ));
        }

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
     */
    public function theFlowIsNotSaved()
    {
        if ($this->response->getStatusCode() !== 400) {
            throw new \RuntimeException(sprintf(
                'Expected a response code 400 but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then the flow is successfully saved
     */
    public function theFlowIsSuccessfullySaved()
    {
        if ($this->response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf(
                'Expected a response code 200 but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then the stored configuration is not empty
     */
    public function theStoredConfigurationIsNotEmpty()
    {
        $flow = $this->flowRepository->find(Uuid::fromString($this->flowUuid));
        $configuration = $flow->getContext()->getConfiguration();

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
    }

    /**
     * @Given I have a flow with UUID :uuid
     */
    public function iHaveAFlowWithUuid($uuid)
    {
        if (null === $this->currentFlow) {
            $this->createFlow(Uuid::fromString($uuid));
        }
    }

    /**
     * @Given I have a flow with the following configuration:
     */
    public function iHaveAFlowWithTheFollowingConfiguration(PyStringNode $string)
    {
        if (null === $this->currentFlow) {
            $this->createFlow(null, Yaml::parse($string->getRaw()));
        }
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
            echo $this->response->getContent();
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
     * @Then I should receive an empty list of environments
     */
    public function iShouldReceiveAnEmptyListOfEnvironments()
    {
        $environments = json_decode($this->response->getContent(), true);

        if (!is_array($environments)) {
            throw new \RuntimeException('The response do not looks like to be a JSON array');
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
     * @param Uuid $uuid
     * @param array $configuration
     * @return Flow
     */
    public function createFlow(Uuid $uuid = null, array $configuration = [])
    {
        $context = $this->createFlowContext($uuid, $configuration);

        $flow = new Flow($context);
        $this->flowRepository->save($flow);


        $this->currentFlow = $flow;

        return $flow;
    }

    /**
     * @param CodeRepository $codeRepository
     *
     * @param Uuid $uuid
     * @param array $configuration
     * @return RiverFlowContext
     */
    private function createFlowContextWithCodeRepository(CodeRepository $codeRepository, Uuid $uuid = null, array $configuration = [])
    {
        $this->flowUuid = (string) ($uuid ?: Uuid::uuid1());
        $user = new User('samuel.roze@gmail.com', Uuid::uuid1());

        $this->codeRepositoryRepository->add($codeRepository);
        $this->authenticatorClient->addUser($user);

        return RiverFlowContext::createFlow(
            Uuid::fromString($this->flowUuid),
            $user,
            $codeRepository,
            $configuration
        );
    }

    /**
     * @return Flow
     */
    public function getCurrentFlow()
    {
        return $this->currentFlow;
    }

    /**
     * @param Uuid $uuid
     * @param array $configuration
     * @return RiverFlowContext
     */
    private function createFlowContext(Uuid $uuid = null, array $configuration = [])
    {
        return $this->createFlowContextWithCodeRepository(new CodeRepository\GitHub\GitHubCodeRepository(
            new Repository('foo', 'bar')
        ), $uuid, $configuration);
    }
}
