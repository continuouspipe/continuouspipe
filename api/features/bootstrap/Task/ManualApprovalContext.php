<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\Task\ManualApproval\ManualApprovalTask;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\Security\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class ManualApprovalContext implements Context
{
    /**
     * @var \Tide\TasksContext
     */
    private $tideTasksContext;

    /**
     * @var \TideContext
     */
    private $tideContext;

    /**
     * @var \FlowContext
     */
    private $flowContext;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
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
     * @When I approve the task
     */
    public function iApproveTheTask()
    {
        $this->iChoose('approve');
    }

    /**
     * @When I reject the task
     */
    public function iRejectTheTask()
    {
        $this->iChoose('reject');
    }

    /**
     * @Then the manual approval task should be successful
     */
    public function theManualApprovalTaskShouldBeSuccessful()
    {
        $this->assertTaskStatus(Task::STATUS_SUCCESSFUL);
    }

    /**
     * @Then the manual approval task should be failed
     */
    public function theManualApprovalTaskShouldBeFailed()
    {
        $this->assertTaskStatus(Task::STATUS_FAILED);
    }

    private function iChoose($decision)
    {
        $tideUuid = (string) $this->tideContext->getCurrentTideUuid();
        $task = $this->getTask();

        $response = $this->kernel->handle(Request::create(
            sprintf('/tides/%s/tasks/%s/'.$decision, $tideUuid, $task->getIdentifier()),
            'POST'
        ));

        $this->assertResponseCode($response, 204);
    }

    /**
     * @return ManualApprovalTask
     */
    private function getTask()
    {
        /* @var ManualApprovalTask[] $tasks */
        $tasks = $this->tideTasksContext->getTasksOfType(ManualApprovalTask::class);

        if (count($tasks) == 0) {
            throw new \RuntimeException('No manual approval task found');
        }

        return current($tasks);
    }

    private function assertTaskStatus($status)
    {
        $foundStatus = $this->getTask()->getStatus();

        if ($foundStatus != $status) {
            throw new \RuntimeException(sprintf(
                'Found status "%s" instead',
                $foundStatus
            ));
        }
    }

    private function assertResponseCode(Response $response, int $code)
    {
        if ($response->getStatusCode() != $code) {
            echo $response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code %s but got %s',
                $code,
                $response->getStatusCode()
            ));
        }
    }
}
