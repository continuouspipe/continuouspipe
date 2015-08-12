<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Tests\CodeRepository\Status\FakeCodeStatusUpdater;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GitHubContext implements Context
{
    /**
     * @var TideContext
     */
    private $tideContext;

    /**
     * @var FlowContext
     */
    private $flowContext;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var FakeCodeStatusUpdater
     */
    private $fakeCodeStatusUpdater;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param Kernel $kernel
     * @param FakeCodeStatusUpdater $fakeCodeStatusUpdater
     */
    public function __construct(Kernel $kernel, FakeCodeStatusUpdater $fakeCodeStatusUpdater)
    {
        $this->fakeCodeStatusUpdater = $fakeCodeStatusUpdater;
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
        $this->flowContext = $scope->getEnvironment()->getContext('FlowContext');
    }

    /**
     * @Then the GitHub commit status should be :status
     */
    public function theGitHubCommitStatusShouldBe($status)
    {
        $foundStatus = $this->fakeCodeStatusUpdater->getStatusForTideUuid(
            $this->tideContext->getCurrentTideUuid()
        );

        if ($status !== $foundStatus) {
            throw new \RuntimeException(sprintf(
                'Found status "%s" instead of expected "%s"',
                $foundStatus,
                $status
            ));
        }
    }

    /**
     * @When a push webhook is received
     */
    public function aPushWebhookIsReceived()
    {
        $contents = file_get_contents(__DIR__.'/../fixtures/push-master.json');
        $flowUuid = $this->flowContext->getCurrentUuid();
        $this->response = $this->kernel->handle(Request::create('/web-hook/github/'.$flowUuid, 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_GITHUB_EVENT' => 'push',
            'HTTP_X_GITHUB_DELIVERY' => '1234',
        ], $contents));
    }

    /**
     * @Then the created tide UUID should be returned
     */
    public function theCreatedTideUuidShouldBeReturned()
    {
        if (200 !== $this->response->getStatusCode()) {
            echo $this->response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected status code 200 but got %d',
                $this->response->getStatusCode()
            ));
        }

        $json = json_decode($this->response->getContent(), true);
        $uuid = $json['uuid'];
        $this->tideContext->setCurrentTideUuid(\Rhumsaa\Uuid\Uuid::fromString($uuid));
    }
}
