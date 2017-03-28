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
use ContinuousPipe\River\Task\Task;
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
     * @When the first run failed
     */
    public function theFirstRunFailed()
    {
        $deployments = $this->traceablePipeClient->getDeployments();
        if (0 === count($deployments)) {
            throw new \RuntimeException('No deployment found');
        }

        /** @var RunTask $task */
        $task = $this->tideTasksContext->getTasksOfType(RunTask::class)[0];
        $this->sendRunTaskNotification($task, $deployments[0]->getRequest(), Deployment::STATUS_FAILURE);
    }

    /**
     * @When the second run task succeed
     */
    public function theSecondRunTaskSucceed()
    {
        $deployments = $this->traceablePipeClient->getDeployments();

        /** @var RunTask $task */
        $task = $this->tideTasksContext->getTasksOfType(RunTask::class)[1];
        $matchingDeployments = array_values(array_filter($deployments, function(Deployment $deployment) use ($task) {
            return null !== $task->getStartedRunUuid() && $deployment->getUuid()->equals($task->getStartedRunUuid());
        }));

        if (1 != count($matchingDeployments)) {
            throw new \RuntimeException(sprintf(
                'Found %d deployments, expected 1',
                count($deployments)
            ));
        }

        $this->sendRunTaskNotification($task, $matchingDeployments[0]->getRequest(), Deployment::STATUS_SUCCESS);
    }

    /**
     * @Then the run task should be failed
     */
    public function theRunTaskShouldBeFailed()
    {
        if ($this->getRunTask()->getStatus() != Task::STATUS_FAILED) {
            throw new \RuntimeException('Expected the task to be failed');
        }
    }

    /**
     * @Then the run task should be successful
     */
    public function theRunTaskShouldBeSuccessful()
    {
        if ($this->getRunTask()->getStatus() != Task::STATUS_SUCCESSFUL) {
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
            throw new \RuntimeException('The component does not have a deployment strategy');
        }

        if (!$deploymentStrategy->isAttached()) {
            throw new \RuntimeException('Component is not deployed as attached');
        }
    }

    /**
     * @Then the component :name should be deployed as not scaling
     */
    public function theComponentShouldBeDeployedAsNotScaling($name)
    {
        $component = $this->getDeployedComponentNamed($name);

        if ($component->getSpecification()->getScalability()->isEnabled()) {
            throw new \RuntimeException('Component is deployed as scaling');
        }
    }

    /**
     * @Then the component :name should be deployed as scaling
     */
    public function theComponentShouldBeDeployedAsScaling($name)
    {
        $component = $this->getDeployedComponentNamed($name);

        if (!$component->getSpecification()->getScalability()->isEnabled()) {
            throw new \RuntimeException('Component is deployed as non-scaling');
        }
    }

    /**
     * @Then the component :name should be deployed with an unknown number of replicas
     */
    public function theComponentShouldBeDeployedWithAnUnknownNumberOfReplicas($name)
    {
        $component = $this->getDeployedComponentNamed($name);
        $replicas = $component->getSpecification()->getScalability()->getNumberOfReplicas();

        if (null !== $replicas) {
            throw new \RuntimeException(sprintf(
                'Expected the number of replicas to be null but found %d',
                $replicas
            ));
        }
    }

    /**
     * @Then the component :name should be deployed with :replicas replicas
     */
    public function theComponentShouldBeDeployedWithReplicas($name, $replicas)
    {
        $component = $this->getDeployedComponentNamed($name);
        $foundReplicas = $component->getSpecification()->getScalability()->getNumberOfReplicas();

        if ($foundReplicas != $replicas) {
            throw new \RuntimeException(sprintf(
                'Expected the number of replicas to be %d but found %d',
                $replicas,
                $foundReplicas
            ));
        }
    }

    /**
     * @Then the component :name should be deployed with an endpoint named :endpointName
     */
    public function theComponentShouldBeDeployedWithAnEndpointNamed($name, $endpointName)
    {
        $component = $this->getDeployedComponentNamed($name);
        $matching = array_filter($component->getEndpoints(), function(Component\Endpoint $endpoint) use ($endpointName) {
            return $endpoint->getName() == $endpointName;
        });

        if (count($matching) === 0) {
            throw new \RuntimeException('Endpoint not found');
        }
    }

    /**
     * @Then the component :name should be deployed with the following environment variables:
     */
    public function theComponentShouldBeDeployedWithTheFollowingEnvironmentVariables($name, TableNode $table)
    {
        $component = $this->getDeployedComponentNamed($name);
        $foundEnvironmentVariablesAsHash = [];
        foreach ($component->getSpecification()->getEnvironmentVariables() as $environmentVariable) {
            if (array_key_exists($environmentVariable->getName(), $foundEnvironmentVariablesAsHash)) {
                throw new \RuntimeException(sprintf('Variable "%s" defined at least 2 times', $environmentVariable->getName()));
            }

            $foundEnvironmentVariablesAsHash[$environmentVariable->getName()] = $environmentVariable->getValue();
        }

        foreach ($table->getHash() as $row) {
            if (!array_key_exists($row['name'], $foundEnvironmentVariablesAsHash)) {
                throw new \RuntimeException(sprintf('Variable "%s" not found in %s', $row['name'], implode(array_keys($foundEnvironmentVariablesAsHash))));
            }

            $foundValue = $foundEnvironmentVariablesAsHash[$row['name']];
            if ($foundValue != $row['value']) {
                throw new \RuntimeException(sprintf('Found value "%s" instead of "%s" for the variable "%s"', $foundValue, $row['value'], $row['name']));
            }
        }
    }

    /**
     * @Then the endpoint :endpointName of the component :name should be deployed with :count SSL certificate
     */
    public function theEndpointOfTheComponentShouldBeDeployedWithSslCertificate($endpointName, $name, $count)
    {
        $endpoint = $this->getEndpointOfComponent($name, $endpointName);
        $numberOfCertificates = count($endpoint->getSslCertificates());

        if ($numberOfCertificates != $count) {
            throw new \RuntimeException(sprintf(
                'Expected %d certificates but found %d',
                $count,
                $numberOfCertificates
            ));
        }
    }

    /**
     * @Then the endpoint :endpointName of the component :name should be deployed with a CloudFlare DNS zone configuration
     */
    public function theEndpointOfTheComponentShouldBeDeployedWithACloudflareDnsZoneConfiguration($endpointName, $name)
    {
        $endpoint = $this->getEndpointOfComponent($name, $endpointName);

        if (null === ($configuration = $endpoint->getCloudFlareZone())) {
            throw new \RuntimeException('The CloudFlare configuration is null');
        }

        return $configuration;
    }

    /**
     * @Then the endpoint :endpointName of the component :name should be deployed with a proxied CloudFlare DNS zone configuration
     */
    public function theEndpointOfTheComponentShouldBeDeployedWithAProxiedCloudflareDnsZoneConfiguration($endpointName, $name)
    {
        $configuration = $this->theEndpointOfTheComponentShouldBeDeployedWithACloudflareDnsZoneConfiguration($endpointName, $name);

        if (!$configuration->isProxied()) {
            throw new \RuntimeException('The zone is not proxied');
        }
    }

    /**
     * @Then the endpoint :endpointName of the component :name should be deployed with an ingress with the host :host
     */
    public function theEndpointOfTheComponentShouldBeDeployedWithAnIngressWithTheHost($endpointName, $name, $host)
    {
        $endpoint = $this->getEndpointOfComponent($name, $endpointName);

        if (null === ($ingress = $endpoint->getIngress())) {
            throw new \RuntimeException('The ingress configuration is null');
        }

        $foundHosts = [];
        foreach ($ingress->getRules() as $rule) {
            $foundHosts[] = $rule->getHost();

            if ($rule->getHost() == $host) {
                return;
            }
        }

        throw new \RuntimeException(sprintf('Such host not found. Found %s', implode(', ', $foundHosts)));
    }

    /**
     * @Then the endpoint :endpointName of the component :name should be deployed with an HttpLabs configuration for the project :project and API key :apiKey
     */
    public function theEndpointOfTheComponentShouldBeDeployedWithAnHttplabsConfigurationForTheProjectAndApiKey($endpointName, $name, $project, $apiKey)
    {
        $endpoint = $this->getEndpointOfComponent($name, $endpointName);

        if (null === ($httpLabsConfiguration = $endpoint->getHttpLabs())) {
            throw new \RuntimeException('The HttpLabs configuration is null');
        }

        if ($httpLabsConfiguration->getProjectIdentifier() != $project || $httpLabsConfiguration->getApiKey() != $apiKey) {
            throw new \RuntimeException('HttpLabs configuration not matching');
        }
    }

    /**
     * @Then the endpoint :endpointName of the component :name should be deployed with an HttpLabs configuration that have :count middleware
     */
    public function theEndpointOfTheComponentShouldBeDeployedWithAnHttplabsConfigurationThatHaveMiddleware($endpointName, $name, $count)
    {
        $endpoint = $this->getEndpointOfComponent($name, $endpointName);

        if (null === ($httpLabsConfiguration = $endpoint->getHttpLabs())) {
            throw new \RuntimeException('The HttpLabs configuration is null');
        }

        if ($count != count($httpLabsConfiguration->getMiddlewares())) {
            throw new \RuntimeException('Number of middlewares not matching');
        }
    }

    /**
     * @Then the endpoint :endpointName of the component :componentName should be deployed with the following annotations:
     */
    public function theEndpointOfTheComponentShouldBeDeployedWithTheFollowingAnnotations($endpointName, $componentName, TableNode $table)
    {
        $endpoint = $this->getEndpointOfComponent($componentName, $endpointName);
        $annotations = $endpoint->getAnnotations();

        foreach ($table->getHash() as $row) {
            if (!array_key_exists($row['name'], $annotations)) {
                throw new \RuntimeException(sprintf('Annotation named %s not found', $row['name']));
            }

            if ($annotations[$row['name']] != $row['value']) {
                throw new \RuntimeException(sprintf(
                    'Found value "%s" for the annotation "%s"',
                    $annotations[$row['name']],
                    $row['name']
                ));
            }
        }
    }

    /**
     * @Then the component :name should request :request of CPU
     */
    public function theComponentShouldRequestOfCpu($name, $request)
    {
        $component = $this->getDeployedComponentNamed($name);
        $requests = $component->getSpecification()->getResources()->getRequests();

        if ($requests->getCpu() != $request) {
            throw new \RuntimeException(sprintf(
                'Expected %s but found %s',
                $request,
                $requests->getCpu()
            ));
        }
    }

    /**
     * @Then the component :name should be limited to :limit of CPU
     */
    public function theComponentShouldBeLimitedToOfCpu($name, $limit)
    {
        $component = $this->getDeployedComponentNamed($name);
        $limits = $component->getSpecification()->getResources()->getLimits();

        if ($limits->getCpu() != $limit) {
            throw new \RuntimeException(sprintf(
                'Expected %s but found %s',
                $limit,
                $limits->getCpu()
            ));
        }
    }

    /**
     * @Then the component :name should request :request of memory
     */
    public function theComponentShouldRequestOfMemory($name, $request)
    {
        $component = $this->getDeployedComponentNamed($name);
        $requests = $component->getSpecification()->getResources()->getRequests();

        if ($requests->getMemory() != $request) {
            throw new \RuntimeException(sprintf(
                'Expected %s but found %s',
                $request,
                $requests->getMemory()
            ));
        }
    }

    /**
     * @Then the component :name should be limited to :limit of memory
     */
    public function theComponentShouldBeLimitedToOfMemory($name, $limit)
    {
        $component = $this->getDeployedComponentNamed($name);
        $limits = $component->getSpecification()->getResources()->getLimits();

        if ($limits->getMemory() != $limit) {
            throw new \RuntimeException(sprintf(
                'Expected %s but found %s',
                $limit,
                $limits->getMemory()
            ));
        }
    }

    /**
     * @Then the component :name should be reset across deployments
     */
    public function theComponentShouldBeResetAcrossDeployments($name)
    {
        $component = $this->getDeployedComponentNamed($name);
        if (!$component->getDeploymentStrategy()->isReset()) {
            throw new \RuntimeException('Not deployed as reset');
        }
    }

    /**
     * @Then the name of the environment on which the task was run should be :name
     */
    public function theNameOfTheEnvironmentOnWhichTheTaskWasRunShouldBe($name)
    {
        $request = $this->getDeploymentRequest();

        if ($name != $request->getTarget()->getEnvironmentName()) {
            throw new \RuntimeException(sprintf(
                'Expected namespace "%s" but got "%s"',
                $name,
                $request->getTarget()->getEnvironmentName()
            ));
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
        $matchingComponents = array_filter($this->getDeploymentRequest()->getSpecification()->getComponents(), function(Component $component) use ($name) {
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
        $this->sendRunnerNotification(
            new Deployment($task->getStartedRunUuid(), $deploymentRequest, $status)
        );
    }

    /**
     * @return DeploymentRequest
     */
    private function getDeploymentRequest()
    {
        $requests = $this->traceablePipeClient->getRequests();
        if (count($requests) == 0) {
            throw new \RuntimeException('No pipe request found');
        }

        return end($requests);
    }

    /**
     * @param string $name
     * @param string $endpointName
     *
     * @return Component\Endpoint
     */
    private function getEndpointOfComponent($name, $endpointName)
    {
        $component = $this->getDeployedComponentNamed($name);
        $endpoint = current(array_filter($component->getEndpoints(), function (Component\Endpoint $endpoint) use ($endpointName) {
            return $endpoint->getName() == $endpointName;
        }));

        if (false === $endpoint) {
            throw new \RuntimeException('Endpoint not found');
        }

        return $endpoint;
    }
}
