<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\Flow\Task;
use ContinuousPipe\River\Task\Run\RunTask;
use ContinuousPipe\Runner\Tests\TraceableClient;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Tide\TasksContext;

class RunContext implements Context
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
     * @var TasksContext
     */
    private $tideTasksContext;

    /**
     * @var TraceableClient
     */
    private $traceableRunnerClient;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @param Kernel $kernel
     * @param TraceableClient $traceableRunnerClient
     */
    public function __construct(Kernel $kernel, TraceableClient $traceableRunnerClient)
    {
        $this->traceableRunnerClient = $traceableRunnerClient;
        $this->kernel = $kernel;
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
     * @Given a run task is started
     */
    public function aRunTaskIsStarted()
    {
        $this->flowContext->createFlowWithTasks([
            new Task('run', [
                'commands' => 'bin/behat',
                'image' => 'sroze/behat'
            ])
        ]);

        $this->tideContext->aTideIsStartedBasedOnThatWorkflow();
    }

    /**
     * @When a run request should be sent
     */
    public function aRunRequestShouldBeSent()
    {
        $requests = $this->traceableRunnerClient->getRequests();

        if (count($requests) == 0) {
            throw new \RuntimeException('Expected to find runner requests, found 0');
        }
    }

    /**
     * @When the run failed
     */
    public function theRunFailed()
    {
        $this->sendRunnerNotification([
            'uuid' => (string) $this->traceableRunnerClient->getLastUuid(),
            'status' => 'failure'
        ]);
    }

    /**
     * @Then the run task should be failed
     */
    public function theRunTaskShouldBeFailed()
    {
        if (!$this->getRunTask()->isFailed()) {
            throw new \RuntimeException('Expected the task to be failed');
        }
    }

    /**
     * @Then the run task should be successful
     */
    public function theRunTaskShouldBeSuccessful()
    {
        if (!$this->getRunTask()->isSuccessful()) {
            throw new \RuntimeException('Expected the task to be successful, be it\'s not');
        }
    }

    /**
     * @When the run succeed
     */
    public function theRunSucceed()
    {
        $this->sendRunnerNotification([
            'uuid' => (string) $this->traceableRunnerClient->getLastUuid(),
            'status' => 'success'
        ]);
    }

    /**
     * @param array $contents
     */
    private function sendRunnerNotification(array $contents)
    {
        $response = $this->kernel->handle(Request::create(
            sprintf('/runner/notification/tide/%s', (string) $this->tideContext->getCurrentTideUuid()),
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($contents)
        ));

        if (!in_array($response->getStatusCode(), [200, 204])) {
            throw new \RuntimeException(sprintf(
                'Expected status code 200 but got %d',
                $response->getStatusCode()
            ));
        }
    }

    /**
     * @return RunTask
     */
    private function getRunTask()
    {
        /* @var Task[] $deployTasks */
        $runTasks = $this->tideTasksContext->getTasksOfType(RunTask::class);

        if (count($runTasks) == 0) {
            throw new \RuntimeException('No run task found');
        }

        return current($runTasks);
    }
}
