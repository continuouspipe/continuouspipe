<?php

use Behat\Behat\Context\Context;
use Rhumsaa\Uuid\Uuid;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\FlowContext as RiverFlowContext;
use ContinuousPipe\User\User;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use GitHub\WebHook\Model\Repository;

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
     * @param Kernel $kernel
     * @param TokenStorageInterface $tokenStorage
     * @param FlowRepository $flowRepository
     * @param InMemoryCodeRepositoryRepository $codeRepositoryRepository
     */
    public function __construct(Kernel $kernel, TokenStorageInterface $tokenStorage, FlowRepository $flowRepository, InMemoryCodeRepositoryRepository $codeRepositoryRepository)
    {
        $this->flowRepository = $flowRepository;
        $this->kernel = $kernel;
        $this->tokenStorage = $tokenStorage;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
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
            'CONTENT_TYPE' => 'application/json'
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
            new Flow\Task('build')
        ]);
    }

    /**
     * @Given I have a flow with a build task
     */
    public function iHaveAFlowWithADeployTask()
    {
        $this->createFlowWithTasks([
            new Flow\Task('deploy', [
                'providerName' => 'fake/provider'
            ])
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
     * @return Flow
     */
    public function createFlow()
    {
        $context = $this->createFlowContext();

        return $this->createFlowWithContextAndTasks($context, [
            new Flow\Task('build'),
            new Flow\Task('deploy', [
                'providerName' => 'fake/provider'
            ])
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
     * @return RiverFlowContext
     */
    private function createFlowContextWithCodeRepository(CodeRepository $codeRepository)
    {
        $this->flowUuid = Uuid::uuid1();
        $this->codeRepositoryRepository->add($codeRepository);

        return RiverFlowContext::createFlow(
            $this->flowUuid,
            new User('samuel.roze@gmail.com'),
            $codeRepository
        );
    }

    /**
     * @param RiverFlowContext $context
     * @param Flow\Task[] $tasks
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
    private function createFlowContext()
    {
        return $this->createFlowContextWithCodeRepository(new CodeRepository\GitHub\GitHubCodeRepository(
            new Repository('foo', 'bar')
        ));
    }
}
