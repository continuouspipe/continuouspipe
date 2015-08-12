<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\ContextualizedTask;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\Task;
use Rhumsaa\Uuid\Uuid;

class DeployContext implements Context
{
    /**
     * @var \TideContext
     */
    private $tideContext;

    /**
     * @var Task
     */
    private $deployTask;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
    }

    /**
     * @When a deploy task is started
     */
    public function aDeployTaskIsStarted()
    {
        $this->tideContext->aTideIsCreated();
        $tide = $this->tideContext->getCurrentTide();

        /** @var Task[] $deployTasks */
        $deployTasks = array_filter($tide->getTasks()->getTasks(), function(Task $task) {
            if ($task instanceof ContextualizedTask) {
                $task = $task->getTask();
            }

            return $task instanceof DeployTask;
        });

        if (count($deployTasks) == 0) {
            throw new \RuntimeException('No deploy task found');
        }

        $this->deployTask = current($deployTasks);
        $this->deployTask->start($tide->getContext());
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
        $this->deployTask->apply(new DeploymentFailed(Uuid::uuid1()));
    }

    /**
     * @Then the task should be failed
     */
    public function theTaskShouldBeFailed()
    {
        if (!$this->deployTask->isFailed()) {
            throw new \RuntimeException('Expected the task to be failed');
        }
    }

    /**
     * @When the deployment succeed
     */
    public function theDeploymentSucceed()
    {
        $this->deployTask->apply(new DeploymentSuccessful(Uuid::uuid1()));
    }

    /**
     * @Then the task should be successful
     */
    public function theTaskShouldBeSuccessful()
    {
        if (!$this->deployTask->isSuccessful()) {
            throw new \RuntimeException('Expected the task to be failed');
        }
    }
}
