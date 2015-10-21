<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\ComponentStatus;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tests\Pipe\TraceableClient;
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
     * @var TraceableClient
     */
    private $traceablePipeClient;

    /**
     * @var Deployment|null
     */
    private $deployment;

    /**
     * @param EventStore $eventStore
     * @param MessageBus $eventBus
     * @param TraceableClient $traceablePipeClient
     */
    public function __construct(EventStore $eventStore, MessageBus $eventBus, TraceableClient $traceablePipeClient)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->traceablePipeClient = $traceablePipeClient;
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
        $this->tideContext->aTideIsStartedWithADeployTask();
    }

    /**
     * @Then the deployment should be started
     */
    public function theDeploymentShouldBeStarted()
    {
        $events = $this->eventStore->findByTideUuid($this->tideContext->getCurrentTideUuid());
        $deploymentStartedEvents = array_filter($events, function ($event) {
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
     * @When the service :name was created
     */
    public function theServiceWasCreated($name)
    {
        $this->theServiceWasCreatedWithThePublicAddress($name, null);
    }

    /**
     * @When the service :name was not created
     */
    public function theServiceMysqlWasNotCreated($name)
    {
        $this->deployment = $this->getDeployment();
        $componentStatuses = $this->deployment->getComponentStatuses() ?: [];
        $componentStatuses[$name] = new ComponentStatus(false, false, false);

        $this->deployment = new Deployment(
            $this->deployment->getUuid(),
            $this->deployment->getRequest(),
            $this->deployment->getStatus(),
            $this->deployment->getPublicEndpoints() ?: [],
            $componentStatuses
        );
    }

    /**
     * @Given the service :name was created with the public address :address
     */
    public function theServiceWasCreatedWithThePublicAddress($name, $address)
    {
        $this->deployment = $this->getDeployment();
        $componentStatuses = $this->deployment->getComponentStatuses() ?: [];
        $componentStatuses[$name] = new ComponentStatus(true, false, false);
        $publicEndpoints = $this->deployment->getPublicEndpoints() ?: [];

        if ($address !== null) {
            $publicEndpoints[] = new PublicEndpoint($name, $address);
        }

        $this->deployment = new Deployment(
            $this->deployment->getUuid(),
            $this->deployment->getRequest(),
            $this->deployment->getStatus(),
            $publicEndpoints,
            $componentStatuses
        );
    }

    /**
     * @When the deployment succeed
     */
    public function theDeploymentSucceed()
    {
        $this->eventBus->handle(new DeploymentSuccessful(
            $this->tideContext->getCurrentTideUuid(),
            $this->getDeployment()
        ));
    }

    /**
     * @When the deploy task succeed
     */
    public function theDeployTaskSucceed()
    {
        $this->theDeploymentSucceed();
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
     * @Then the component :component should be deployed as accessible from outside
     */
    public function theComponentShouldBeDeployedAsAccessibleFromOutside($componentName)
    {
        $component = $this->getDeployedComponent($componentName);

        if (!$component->getSpecification()->getAccessibility()->isFromExternal()) {
            throw new \RuntimeException('Component is not accessible from outside');
        }
    }

    /**
     * @Then the component :component should be deployed as locked
     */
    public function theComponentShouldBeDeployedAsLocked($componentName)
    {
        $component = $this->getDeployedComponent($componentName);

        if (!$component->isLocked()) {
            throw new \RuntimeException('Component is not locked');
        }
    }

    /**
     * @Then the component :component should not be deployed as accessible from outside
     */
    public function theComponentShouldNotBeDeployedAsAccessibleFromOutside($componentName)
    {
        $component = $this->getDeployedComponent($componentName);

        if ($component->getSpecification()->getAccessibility()->isFromExternal()) {
            throw new \RuntimeException('Component is accessible from outside');
        }
    }

    /**
     * @Then the component :component should be deployed with the image :image
     */
    public function theComponentShouldBeDeployedWithTheImage($componentName, $image)
    {
        $component = $this->getDeployedComponent($componentName);
        $foundImage = $component->getSpecification()->getSource()->getImage();

        if ($image != $foundImage) {
            throw new \RuntimeException(sprintf(
                'Found image "%s" instead',
                $foundImage
            ));
        }
    }

    /**
     * @Then the component :componentName should be deployed with a TCP port :portNumber named :portName opened
     */
    public function theComponentShouldBeDeployedWithATcpPortNamedOpened($componentName, $portNumber, $portName)
    {
        $component = $this->getDeployedComponent($componentName);
        $ports = $component->getSpecification()->getPorts();
        $matchingPorts = array_filter($ports, function(Component\Port $port) use ($portNumber, $portName) {
            return $port->getIdentifier() == $portName && $port->getPort() == $portNumber;
        });

        if (0 == count($matchingPorts)) {
            throw new \RuntimeException('No matching port found');
        }
    }

    /**
     * @Then the name of the deployed environment should be :expectedName
     */
    public function theNameOfTheDeployedEnvironmentShouldBe($expectedName)
    {
        $namingStrategy = new EnvironmentNamingStrategy();
        $foundName = $namingStrategy->getName($this->flowContext->getCurrentUuid(), $this->getDeployTask()->getContext()->getCodeReference());

        if ($foundName != $expectedName) {
            throw new \RuntimeException(sprintf(
                'Found name "%s" while expecting "%s"',
                $foundName,
                $expectedName
            ));
        }
    }

    /**
     * @param string $componentName
     * @return Component
     */
    private function getDeployedComponent($componentName)
    {
        $deploymentRequests = $this->traceablePipeClient->getRequests();
        if (0 == count($deploymentRequests)) {
            throw new \RuntimeException('No deployment request found');
        }

        $deploymentRequest = current($deploymentRequests);
        $components = $deploymentRequest->getSpecification()->getComponents();
        $matchingComponents = array_filter($components, function(Component $component) use ($componentName) {
            return $component->getName() == $componentName;
        });

        if (0 == count($matchingComponents)) {
            throw new \RuntimeException(sprintf(
                'No component named "%s" found in the deployment request',
                $componentName
            ));
        }

        /** @var Component $component */
        $component = current($matchingComponents);

        return $component;
    }

    /**
     * @return DeployTask
     */
    private function getDeployTask()
    {
        /** @var Task[] $deployTasks */
        $deployTasks = $this->tideTasksContext->getTasksOfType(DeployTask::class);
        if (count($deployTasks) == 0) {
            throw new \RuntimeException('No deploy task found');
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
        $deploymentStartedEvents = array_filter($events, function (TideEvent $event) {
            return $event instanceof DeploymentStarted;
        });

        if (count($deploymentStartedEvents) == 0) {
            throw new \RuntimeException('No deployment started event');
        }
        return current($deploymentStartedEvents);
    }

    /**
     * @return Deployment|null
     */
    private function getDeployment()
    {
        if (null === $this->deployment) {
            $this->deployment = $this->getDeploymentStartedEvent()->getDeployment();
        }

        return $this->deployment;
    }
}
