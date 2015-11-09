<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\RunTask;
use ContinuousPipe\River\Tests\Pipe\TraceableClient;
use JMS\Serializer\Serializer;
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
    private $traceablePipeClient;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Kernel $kernel
     * @param TraceableClient $traceablePipeClient
     * @param Serializer $serializer
     */
    public function __construct(Kernel $kernel, TraceableClient $traceablePipeClient, Serializer $serializer)
    {
        $this->traceablePipeClient = $traceablePipeClient;
        $this->kernel = $kernel;
        $this->serializer = $serializer;
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
     * @Given a build and run task is started with a service name
     */
    public function aRunTaskIsStartedWithAServiceName()
    {
        $this->tideContext->aTideIsStartedWithTasks([
            [
                'build' => [],
            ],
            [
                'run' => [
                    'commands' => ['bin/behat'],
                    'image' => ['from_service' => 'image0'],
                    'cluster' => 'foo'
                ]
            ]
        ]);
    }

    /**
     * @Given a run task is started with an image name
     */
    public function aRunTaskIsStartedWithAnImageName()
    {
        $this->tideContext->aTideIsStartedWithTasks([
            [
                'run' => [
                    'commands' => ['bin/behat'],
                    'image' => 'sroze/behat',
                    'cluster' => 'foo'
                ]
            ]
        ]);
    }

    /**
     * @Then a run request should be sent
     */
    public function aRunRequestShouldBeSent()
    {
        $requests = $this->traceablePipeClient->getRequests();

        if (count($requests) == 0) {
            throw new \RuntimeException('Expected to find runner requests, found 0');
        }
    }

    /**
     * @When the run failed
     */
    public function theRunFailed()
    {
        $deployments = $this->traceablePipeClient->getDeployments();
        if (0 === count($deployments)) {
            throw new \RuntimeException('No deployment found');
        }

        $deployment = $deployments[0];
        $this->sendRunnerNotification(
            new Deployment($deployment->getUuid(), $deployment->getRequest(), Deployment::STATUS_FAILURE)
        );
    }

    /**
     * @When the first run succeed
     */
    public function theFirstRunSucceed()
    {
        $deployments = $this->traceablePipeClient->getDeployments();
        if (0 === count($deployments)) {
            throw new \RuntimeException('No deployment found');
        }

        /** @var RunTask $task */
        $task = $this->tideTasksContext->getTasksOfType(RunTask::class)[0];
        $this->sendRunTaskNotification($task, $deployments[0]->getRequest(), Deployment::STATUS_SUCCESS);
    }

    /**
     * @When the second run task succeed
     */
    public function theSecondRunTaskSucceed()
    {
        $deployments = $this->traceablePipeClient->getDeployments();
        if (1 >= count($deployments)) {
            throw new \RuntimeException('1 or 0 deployment found, expected at least 2');
        }

        /** @var RunTask $task */
        $task = $this->tideTasksContext->getTasksOfType(RunTask::class)[1];
        $this->sendRunTaskNotification($task, $deployments[1]->getRequest(), Deployment::STATUS_SUCCESS);
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
        $deployments = $this->traceablePipeClient->getDeployments();
        if (0 === count($deployments)) {
            throw new \RuntimeException('No deployment found');
        }

        $deployment = $deployments[0];
        $this->sendRunnerNotification(
            new Deployment($deployment->getUuid(), $deployment->getRequest(), Deployment::STATUS_SUCCESS)
        );
    }

    /**
     * @Then the component :name should be deployed as attached
     */
    public function theComponentShouldBeDeployedAsAttached($name)
    {
        $component = $this->getDeployedComponentNamed($name);

        if (null === ($deploymentStrategy = $component->getDeploymentStrategy())) {
            throw new \RuntimeException('The component do not have any deployment strategy');
        }

        if (!$deploymentStrategy->isAttached()) {
            throw new \RuntimeException('Component is not deployed as attached');
        }
    }

    /**
     * @Then the component :name should be deployed as not scaling
     */
    public function thePodShouldBeDeployedAsNotRestartingAfterTermination($name)
    {
        $component = $this->getDeployedComponentNamed($name);

        if ($component->getSpecification()->getScalability()->isEnabled()) {
            throw new \RuntimeException('Component is deployed as scaling');
        }
    }

    /**
     * @Then the commands should be run with the following environment variables:
     */
    public function theCommandsShouldBeRunWithTheFollowingEnvironmentVariables(TableNode $table)
    {
        $requests = $this->traceablePipeClient->getRequests();

        /** @var Component[] $components */
        $components = array_reduce($requests, function($carry, DeploymentRequest $request) {
            return array_merge($carry, $request->getSpecification()->getComponents());
        }, []);

        if (count($components) == 0) {
            throw new \RuntimeException(sprintf('Found 0 deployment components (%d requests)', count($requests)));
        }

        foreach ($components as $component) {
            $componentVariables = [];
            foreach ($component->getSpecification()->getEnvironmentVariables() as $foundVariable) {
                $componentVariables[$foundVariable->getName()] = $foundVariable->getValue();
            }

            foreach ($table->getHash() as $environ) {
                if (!array_key_exists($environ['name'], $componentVariables)) {
                    throw new \RuntimeException(sprintf(
                        'Environment variable "%s" not found in component "%s"',
                        $environ['name'],
                        $component->getName()
                    ));
                }

                $foundValue = $componentVariables[$environ['name']];
                if ($foundValue != $environ['value']) {
                    throw new \RuntimeException(sprintf(
                        'Environment variable "%s" found in component "%s" have value "%s" while expecting "%s"',
                        $environ['name'],
                        $component->getName(),
                        $foundValue,
                        $environ['value']
                    ));
                }
            }
        }
    }

    /**
     * @param Deployment $deployment
     */
    private function sendRunnerNotification(Deployment $deployment)
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
            $this->serializer->serialize($deployment, 'json')
        ));

        if (!in_array($response->getStatusCode(), [200, 204])) {
            echo $response->getContent();
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

    /**
     * @param string $name
     *
     * @return Component
     */
    private function getDeployedComponentNamed($name)
    {
        $requests = $this->traceablePipeClient->getRequests();
        if (count($requests) == 0) {
            throw new \RuntimeException('No pipe request found');
        }

        $matchingComponents = array_filter($requests[0]->getSpecification()->getComponents(), function(Component $component) use ($name) {
            return $component->getName() == $name;
        });

        if (0 == count($matchingComponents)) {
            throw new \RuntimeException(sprintf(
                'No component named "%s" found in deployment request',
                $name
            ));
        }

        return current($matchingComponents);
    }

    /**
     * @param RunTask $task
     * @param DeploymentRequest $deploymentRequest
     * @param string $status
     */
    private function sendRunTaskNotification(RunTask $task, DeploymentRequest $deploymentRequest, $status)
    {
        $events = $this->tideTasksContext->getTaskEvents($task);
        $runStartedEvents = array_values(array_filter($events->getEvents(), function($event) {
            return $event instanceof RunStarted;
        }));

        if (0 === count($runStartedEvents)) {
            throw new \RuntimeException('No run started events');
        }

        /** @var RunStarted $runStartedEvent */
        $runStartedEvent = $runStartedEvents[0];

        $this->sendRunnerNotification(
            new Deployment($runStartedEvent->getRunUuid(), $deploymentRequest, $status)
        );
    }
}
