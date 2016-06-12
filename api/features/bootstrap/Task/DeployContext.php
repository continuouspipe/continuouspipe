<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Extension\ReverseProxy\ReverseProxyExtension;
use ContinuousPipe\Pipe\Client\ComponentStatus;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
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
use JMS\Serializer\Serializer;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

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
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @var Deployment|null
     */
    private $deployment;
    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param EventStore $eventStore
     * @param MessageBus $eventBus
     * @param TraceableClient $traceablePipeClient
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param Kernel $kernel
     * @param Serializer $serializer
     */
    public function __construct(EventStore $eventStore, MessageBus $eventBus, TraceableClient $traceablePipeClient, EnvironmentNamingStrategy $environmentNamingStrategy, Kernel $kernel, Serializer $serializer)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->traceablePipeClient = $traceablePipeClient;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
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
     * @When a deploy task is started
     */
    public function aDeployTaskIsStarted()
    {
        $this->tideContext->aTideIsStartedWithADeployTask();
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
     * @When the first deploy succeed
     */
    public function theFirstDeploySucceed()
    {
        $deployments = $this->traceablePipeClient->getDeployments();
        if (0 === count($deployments)) {
            throw new \RuntimeException('No deployment found');
        }

        /** @var DeployTask $task */
        $task = $this->tideTasksContext->getTasksOfType(DeployTask::class)[0];
        $this->sendDeployTaskNotification($task, Deployment::STATUS_SUCCESS);
    }

    /**
     * @When the second deploy succeed
     */
    public function theSecondDeploySucceed()
    {
        $deployments = $this->traceablePipeClient->getDeployments();
        if (1 >= count($deployments)) {
            throw new \RuntimeException('Found 0 or 1 deployment, expected at least 2');
        }

        /** @var DeployTask $task */
        $task = $this->tideTasksContext->getTasksOfType(DeployTask::class)[1];
        $this->sendDeployTaskNotification($task, Deployment::STATUS_SUCCESS);
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
     * @Then the component :componentName should be deployed with a TCP port :portNumber
     */
    public function theComponentShouldBeDeployedWithATcpPort($componentName, $portNumber)
    {
        $component = $this->getDeployedComponent($componentName);
        $ports = $component->getSpecification()->getPorts();
        $matchingPorts = array_filter($ports, function(Component\Port $port) use ($portNumber) {
            return $port->getPort() == $portNumber;
        });

        if (0 == count($matchingPorts)) {
            throw new \RuntimeException('No matching port found');
        }
    }

    /**
     * @Then the component :componentName should have a persistent volume mounted at :mountPath
     */
    public function theComponentShouldHaveAPersistentVolumeMountedAt($componentName, $mountPath)
    {
        $component = $this->getDeployedComponent($componentName);
        $volumeMounts = $component->getSpecification()->getVolumeMounts();
        $matchingVolumeMounts = array_filter($volumeMounts, function(Component\VolumeMount $volumeMount) use ($mountPath) {
            return $volumeMount->getMountPath() == $mountPath;
        });

        if (0 == count($matchingVolumeMounts)) {
            throw new \RuntimeException(sprintf('No volume mount on path "%s"', $mountPath));
        }

        $volumeName = $matchingVolumeMounts[0]->getName();
        $matchingVolumes = array_filter($component->getSpecification()->getVolumes(), function(Component\Volume $volume) use ($volumeName) {
            return $volume->getName() == $volumeName;
        });

        if (0 === count($matchingVolumes)) {
            throw new \RuntimeException(sprintf('No volume named "%s" found', $volumeName));
        }
    }

    /**
     * @Then the component :componentName should be deployed
     */
    public function theComponentShouldBeDeployed($componentName)
    {
        $this->getDeployedComponent($componentName);
    }

    /**
     * @Then the component :componentName should be deployed with the reverse proxy extension and contains the domain name :domainName
     */
    public function theComponentShouldBeDeployedWithTheReverseProxyExtensionAndContainsTheDomainName($componentName, $domainName)
    {
        $component = $this->getDeployedComponent($componentName);
        /** @var $extension ReverseProxyExtension */
        if (null === ($extension = $component->getExtension('reverse_proxy'))) {
            throw new \RuntimeException('Extension "reverse_proxy" not found');
        }

        if (!in_array($domainName, $extension->getDomainNames())) {
            throw new \RuntimeException(sprintf(
                'Domain name not found, but found %d (%s)',
                count($extension->getDomainNames()),
                implode(', ', $extension->getDomainNames())
            ));
        }
    }

    /**
     * @Then the component :componentName should not be deployed
     */
    public function theComponentShouldNotBeDeployed($componentName)
    {
        try {
            $this->getDeployedComponent($componentName);
            $found = true;
        } catch (\RuntimeException $e) {
            $found = false;
        }

        if ($found) {
            throw new \RuntimeException(sprintf(
                'Component "%s" found',
                $componentName
            ));
        }
    }

    /**
     * @Then the readiness probe of the component :name should be an http probe on path :path
     */
    public function theReadinessProbeOfTheComponentShouldBeAnHttpProbeOnPath($name, $path)
    {
        if (null === ($probe = $this->getDeployedComponent($name)->getDeploymentStrategy()->getReadinessProbe())) {
            throw new \RuntimeException('No readiness probe found');
        }

        if (!$probe instanceof Component\Probe\Http) {
            throw new \RuntimeException('Not an HTTP probe');
        }

        if ($path != $probe->getPath()) {
            throw new \RuntimeException(sprintf(
                'Found path "%s"',
                $probe->getPath()
            ));
        }
    }

    /**
     * @Then the readiness probe of the component :name should be a tcp probe on port :port
     */
    public function theReadinessProbeOfTheComponentShouldBeATcpProbeOnPort($name, $port)
    {
        if (null === ($probe = $this->getDeployedComponent($name)->getDeploymentStrategy()->getReadinessProbe())) {
            throw new \RuntimeException('No readiness probe found');
        }

        if (!$probe instanceof Component\Probe\Tcp) {
            throw new \RuntimeException('Not a TCP probe');
        }

        if ($port != $probe->getPort()) {
            throw new \RuntimeException(sprintf(
                'Found port "%s"',
                $probe->getPort()
            ));
        }
    }

    /**
     * @Then the readiness probe of the component :name should be an exec probe with the command :command
     */
    public function theReadinessProbeOfTheComponentShouldBeAnExecProbeWithTheCommand($name, $command)
    {
        if (null === ($probe = $this->getDeployedComponent($name)->getDeploymentStrategy()->getReadinessProbe())) {
            throw new \RuntimeException('No readiness probe found');
        }

        if (!$probe instanceof Component\Probe\Exec) {
            throw new \RuntimeException('Not a EXEC probe');
        }

        $command = explode(',', $command);
        if ($command != $probe->getCommand()) {
            throw new \RuntimeException(sprintf(
                'Found command "%s"',
                implode(',', $probe->getCommand())
            ));
        }
    }

    /**
     * @Then the name of the deployed environment should be :expectedName
     */
    public function theNameOfTheDeployedEnvironmentShouldBe($expectedName)
    {
        $foundName = $this->environmentNamingStrategy->getName(
            $this->tideContext->getCurrentTideUuid(), $this->getDeployTask()->getConfiguration()->getEnvironmentName()
        );

        if ($foundName != $expectedName) {
            throw new \RuntimeException(sprintf(
                'Found name "%s" while expecting "%s"',
                $foundName,
                $expectedName
            ));
        }
    }

    /**
     * @Then the name of the deployed environment should not be :name
     */
    public function theNameOfTheDeployedEnvironmentShouldNotBe($name)
    {
        $foundName = $this->environmentNamingStrategy->getName(
            $this->tideContext->getCurrentTideUuid(),
            $this->getDeployTask()->getConfiguration()->getEnvironmentName()
        );

        if ($foundName == $name) {
            throw new \RuntimeException(sprintf(
                'Found name "%s" while expecting not to be that',
                $foundName
            ));
        }
    }

    /**
     * @Then the name of the deployed environment should be less or equals than :characters characters long
     */
    public function theNameOfTheDeployedEnvironmentShouldBeLessOrEqualsThanCharactersLong($characters)
    {
        $foundName = $this->environmentNamingStrategy->getName(
            $this->tideContext->getCurrentTideUuid(),
            $this->getDeployTask()->getConfiguration()->getEnvironmentName()
        );

        if (strlen($foundName) > $characters) {
            throw new \RuntimeException(sprintf(
                'Expected the name to be less then %d characters, but found %d',
                $characters,
                strlen($foundName)
            ));
        }
    }

    /**
     * @Then the deployed environment should have the tag :tag
     */
    public function theDeployedEnvironmentShouldHaveTheTag($tag)
    {
        $deploymentRequest = $this->getLastDeploymentRequest();
        $environmentLabels = $deploymentRequest->getTarget()->getEnvironmentLabels();

        list($key, $value) = explode('=', $tag);

        if (!array_key_exists($key, $environmentLabels)) {
            throw new \RuntimeException(sprintf('Label "%s" is not found', $key));
        } else if ($environmentLabels[$key] != $value) {
            throw new \RuntimeException(sprintf(
                'Expected value "%s" but found "%s"',
                $value,
                $environmentLabels[$key]
            ));
        }
    }

    /**
     * @param string $componentName
     * @return Component
     */
    private function getDeployedComponent($componentName)
    {
        $deploymentRequest = $this->getLastDeploymentRequest();
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

    /**
     * @param DeployTask $task
     * @param $status
     */
    private function sendDeployTaskNotification(DeployTask $task, $status)
    {
        $events = $this->tideTasksContext->getTaskEvents($task);
        $deploymentStartedEvents = array_values(array_filter($events->getEvents(), function($event) {
            return $event instanceof DeploymentStarted;
        }));

        if (0 === count($deploymentStartedEvents)) {
            throw new \RuntimeException('No deploy started events');
        }

        /** @var DeploymentStarted $deploymentStartedEvent */
        $deploymentStartedEvent = $deploymentStartedEvents[0];

        $this->sendRunnerNotification(
            new Deployment(
                $deploymentStartedEvent->getDeployment()->getUuid(),
                $deploymentStartedEvent->getDeployment()->getRequest(),
                $status
            )
        );
    }

    /**
     * @param Deployment $deployment
     */
    private function sendRunnerNotification(Deployment $deployment)
    {
        $response = $this->kernel->handle(Request::create(
            sprintf('/pipe/notification/tide/%s', (string)$this->tideContext->getCurrentTideUuid()),
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
     * @return DeploymentRequest
     */
    private function getLastDeploymentRequest()
    {
        $deploymentRequests = $this->traceablePipeClient->getRequests();
        if (0 == count($deploymentRequests)) {
            throw new \RuntimeException('No deployment request found');
        }

        return array_pop($deploymentRequests);
    }
}
