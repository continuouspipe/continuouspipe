<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\ContextualizedTask;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\Task;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class DeployContext implements Context
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
     * @var \Tide\TasksContext
     */
    private $tideTasksContext;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param EventStore $eventStore
     * @param MessageBus $eventBus
     */
    public function __construct(EventStore $eventStore, MessageBus $eventBus)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
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
     * @When a deploy task is started
     */
    public function aDeployTaskIsStarted()
    {
        $this->flowContext->iHaveAFlowWithADeployTask();
        $this->tideContext->aTideIsStartedBasedOnThatWorkflow();
    }

    /**
     * @Then the deployment should be started
     */
    public function theDeploymentShouldBeStarted()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $deploymentStartedEvents = array_filter($events, function($event) {
            return $event instanceof DeploymentStarted;
        });

        if (1 !== count($deploymentStartedEvents)) {
            throw new \RuntimeException(sprintf(
                'Expected 1 deployment started event, found %d.',
                count($deploymentStartedEvents)
            ));
        }
    }

    /**
     * @When the deployment failed
     */
    public function theDeploymentFailed()
    {
        $this->eventBus->handle(new DeploymentFailed(
            $this->tideContext->getCurrentTideUuid(),
            $this->getDeploymentStartedEvent()->getDeployment()
        ));
    }

    /**
     * @Then the deploy task should be failed
     */
    public function theTaskShouldBeFailed()
    {
        if (!$this->getDeployTask()->isFailed()) {
            throw new \RuntimeException('Expected the task to be failed');
        }
    }

    /**
     * @When the deployment succeed
     */
    public function theDeploymentSucceed()
    {
        $this->eventBus->handle(new DeploymentSuccessful(
            $this->tideContext->getCurrentTideUuid(),
            $this->getDeploymentStartedEvent()->getDeployment()
        ));
    }

    /**
     * @Then the deploy task should be successful
     */
    public function theTaskShouldBeSuccessful()
    {
        if (!$this->getDeployTask()->isSuccessful()) {
            throw new \RuntimeException('Expected the task to be successful');
        }
    }

    /**
     * @return DeployTask
     */
    private function getDeployTask()
    {
        /** @var Task[] $deployTasks */
        $deployTasks = $this->tideTasksContext->getTasksOfType(DeployTask::class);
        if (count($deployTasks) == 0) {
            throw new \RuntimeException('No build task found');
        }

        return current($deployTasks);
    }

    /**
     * @return DeploymentStarted
     */
    private function getDeploymentStartedEvent()
    {
        $events = $this->eventStore->findByTideUuid(
            $this->tideContext->getCurrentTideUuid()
        );

        /** @var DeploymentStarted[] $deploymentStartedEvents */
        $deploymentStartedEvents = array_filter($events, function(TideEvent $event) {
            return $event instanceof DeploymentStarted;
        });

        return current($deploymentStartedEvents);
    }
}
