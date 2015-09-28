<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\DeploymentSuccessful;
use ContinuousPipe\Pipe\Tests\Adapter\Fake\FakeEnvironmentClient;
use ContinuousPipe\Pipe\Tests\Notification\TraceableNotifier;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use ContinuousPipe\User\User;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\Pipe\EventBus\EventStore;
use Rhumsaa\Uuid\Uuid;

class EnvironmentContext implements Context
{
    /**
     * @var ProviderContext
     */
    private $providerContext;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var Uuid
     */
    private $lastDeploymentUuid;

    /**
     * @var string
     */
    private $deploymentEnvironmentName;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @var TraceableNotifier
     */
    private $notifier;

    /**
     * @var FakeEnvironmentClient
     */
    private $fakeEnvironmentClient;

    /**
     * @param Kernel $kernel
     * @param EventStore $eventStore
     * @param DeploymentRepository $deploymentRepository
     * @param MessageBus $eventBus
     * @param TraceableNotifier $notifier
     * @param FakeEnvironmentClient $fakeEnvironmentClient
     */
    public function __construct(Kernel $kernel, EventStore $eventStore, DeploymentRepository $deploymentRepository, MessageBus $eventBus, TraceableNotifier $notifier, FakeEnvironmentClient $fakeEnvironmentClient)
    {
        $this->kernel = $kernel;
        $this->eventStore = $eventStore;
        $this->deploymentRepository = $deploymentRepository;
        $this->eventBus = $eventBus;
        $this->notifier = $notifier;
        $this->fakeEnvironmentClient = $fakeEnvironmentClient;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->providerContext = $scope->getEnvironment()->getContext('ProviderContext');
    }

    /**
     * @When I send a valid deployment request
     */
    public function iSendAValidDeploymentRequest()
    {
        $this->providerContext->iHaveAFakeProviderNamed('foo');
        $this->sendDeploymentRequest('fake/foo', 'foo');
    }

    /**
     * @Then the environment should be created or updated
     */
    public function theEnvironmentShouldBeCreatedOrUpdated()
    {
        $events = $this->eventStore->findByDeploymentUuid(
            $this->lastDeploymentUuid
        );

        if (0 === count($events)) {
            throw new \RuntimeException('Expected to have at least one event for this deployment, found 0');
        }
    }

    /**
     * @Then the deployment should be successful
     */
    public function theDeploymentShouldBeSuccessful()
    {
        $events = $this->eventStore->findByDeploymentUuid(
            $this->lastDeploymentUuid
        );

        $deploymentSuccessfulEvents = array_filter($events, function ($event) {
            return $event instanceof DeploymentSuccessful;
        });

        if (count($deploymentSuccessfulEvents) == 0) {
            throw new \RuntimeException('No event successful deployment found');
        }
    }

    /**
     * @Then the deployment should be failed
     */
    public function theDeploymentShouldBeFailed()
    {
        $events = $this->eventStore->findByDeploymentUuid(
            $this->lastDeploymentUuid
        );

        $deploymentFailedEvents = array_filter($events, function ($event) {
            return $event instanceof DeploymentFailed;
        });

        if (count($deploymentFailedEvents) == 0) {
            throw new \RuntimeException('No event failed deployment found');
        }
    }

    /**
     * @param string $providerName
     * @param string $environmentName
     * @param string $template
     */
    public function sendDeploymentRequest($providerName, $environmentName, $template = 'simple-app')
    {
        $simpleAppComposeContents = json_decode(file_get_contents(__DIR__.'/../fixtures/'.$template.'.json'), true);
        $contents = json_encode([
            'target' => [
                'environmentName' => $environmentName,
                'providerName' => $providerName,
            ],
            'specification' => [
                'components' => $simpleAppComposeContents,
            ],
        ]);

        $this->response = $this->kernel->handle(Request::create('/deployments', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $contents));

        if (200 !== $this->response->getStatusCode()) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf('Expected response code 200, got %d', $this->response->getStatusCode()));
        }

        $deployment = json_decode($this->response->getContent(), true);
        if (Deployment::STATUS_SUCCESS != $deployment['status']) {
            throw new \RuntimeException(sprintf(
                'Expected deployment status to be "%s" but got "%s"',
                Deployment::STATUS_SUCCESS,
                $deployment['status']
            ));
        }

        $this->lastDeploymentUuid = Uuid::fromString($deployment['uuid']);
        $this->deploymentEnvironmentName = $environmentName;
    }

    /**
     * @Given I have a running deployment
     */
    public function iHaveARunningDeployment()
    {
        $deployment = $this->deploymentRepository->save(
            Deployment::fromRequest(
                new DeploymentRequest(
                    new DeploymentRequest\Target(),
                    new DeploymentRequest\Specification(),
                    new DeploymentRequest\Notification(
                        'http://foo/bar'
                    )
                ),
                new User('sroze@inviqa.com')
            )
        );

        $this->lastDeploymentUuid = $deployment->getUuid();
    }

    /**
     * @When the deployment is successful
     */
    public function theDeploymentIsSuccessful()
    {
        $this->eventBus->handle(new DeploymentSuccessful($this->lastDeploymentUuid));
    }

    /**
     * @When the deployment is failed
     */
    public function theDeploymentIsFailed()
    {
        $this->eventBus->handle(new DeploymentFailed($this->lastDeploymentUuid));
    }

    /**
     * @Then a notification should be sent back
     */
    public function aNotificationShouldBeSentBack()
    {
        $notifications = $this->notifier->getNotifications();
        if (0 == count($notifications)) {
            throw new \RuntimeException('Expecting 1 or more notifications, found 0');
        }
    }

    /**
     * @When I send a deployment request without a given target
     */
    public function iSendADeploymentRequestWithoutAGivenTarget()
    {
        $simpleAppComponents = json_decode(file_get_contents(__DIR__.'/../fixtures/simple-app.json'), true);
        $contents = json_encode([
            'specification' => [
                'components' => $simpleAppComponents,
            ],
        ], true);
        $this->response = $this->kernel->handle(Request::create('/deployments', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $contents));
    }

    /**
     * @Then the validation should fail
     */
    public function theValidationShouldFail()
    {
        if ($this->response->getStatusCode() !== 400) {
            throw new \RuntimeException(sprintf(
                'Expected the response to be 400, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Given I have an environment :name
     */
    public function iHaveAnEnvironment($name)
    {
        $this->fakeEnvironmentClient->add(new Environment($name, $name));
    }

    /**
     * @Then the environment :name shouldn't exists
     */
    public function theEnvironmentShouldnTExists($name)
    {
        $matchingEnvironments = array_filter($this->fakeEnvironmentClient->findAll(), function(Environment $environment) use ($name) {
            return $environment->getName() == $name;
        });

        if (count($matchingEnvironments) != 0) {
            throw new \RuntimeException(sprintf(
                'Found an environment named "%s"',
                $name
            ));
        }
    }

    /**
     * @When I delete the environment named :name of provider :providerName
     */
    public function iDeleteTheEnvironmentNamedOfProvider($name, $providerName, $type = 'fake')
    {
        $response = $this->kernel->handle(Request::create(sprintf(
            '/providers/%s/%s/environments/%s',
            $type,
            $providerName,
            $name
        ), 'DELETE'));

        if (!in_array($response->getStatusCode(), [200, 204])) {
            throw new \RuntimeException(sprintf(
                'Expected response 200 or 204, got %d',
                $response->getStatusCode()
            ));
        }
    }

    /**
     * @Then I should see the component :name
     */
    public function iShouldSeeTheComponentInEnvironment($name)
    {
        $this->getComponentFromListResponse($name);
    }

    /**
     * @Then the status of the component :name should contain the public endpoint :endpoint
     */
    public function theStatusOfTheComponentShouldContainThePublicEndpoint($name, $endpoint)
    {
        $component = $this->getComponentFromListResponse($name);

        if ($endpoint != $component['status']['public_endpoints'][0]) {
            throw new \RuntimeException('Public endpoint was not found');
        }
    }

    /**
     * @Then the status of the component :name should be :status
     */
    public function theStatusOfTheComponentShouldBe($name, $status)
    {
        $component = $this->getComponentFromListResponse($name);
        $foundStatus = $component['status']['status'];

        if ($foundStatus != $status) {
            throw new \RuntimeException(sprintf(
                'Found status "%s" while expecting "%s"',
                $foundStatus,
                $status
            ));
        }
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    private function getEnvironmentFromListResponse($identifier = null)
    {
        $identifier = $identifier ?: $this->deploymentEnvironmentName;
        $response = $this->providerContext->getLastResponseJson();
        if (!is_array($response)) {
            throw new \RuntimeException('Expecting an array, got something else');
        }

        $matchingEnvironments = array_filter($response, function($environment) use ($identifier) {
            return $environment['identifier'] == $identifier;
        });

        if (0 == count($matchingEnvironments)) {
            throw new \RuntimeException(sprintf(
                'No environment named "%s" found',
                $identifier
            ));
        }

        return current($matchingEnvironments);
    }

    /**
     * @param string $name
     * @return array
     */
    private function getComponentFromListResponse($name)
    {
        $environment = $this->getEnvironmentFromListResponse();
        $components = $environment['components'];
        $matchingComponents = array_filter($components, function($component) use ($name) {
            return $component['name'] == $name;
        });

        if (0 == count($matchingComponents)) {
            throw new \RuntimeException(sprintf('No component named "%s" found in the environment', $name));
        }

        return current($matchingComponents);
    }
}
