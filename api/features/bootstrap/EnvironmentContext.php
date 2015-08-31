<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\DeploymentSuccessful;
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
     * @param Kernel               $kernel
     * @param EventStore           $eventStore
     * @param DeploymentRepository $deploymentRepository
     * @param MessageBus           $eventBus
     * @param TraceableNotifier    $notifier
     */
    public function __construct(Kernel $kernel, EventStore $eventStore, DeploymentRepository $deploymentRepository, MessageBus $eventBus, TraceableNotifier $notifier)
    {
        $this->kernel = $kernel;
        $this->eventStore = $eventStore;
        $this->deploymentRepository = $deploymentRepository;
        $this->eventBus = $eventBus;
        $this->notifier = $notifier;
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
            throw new \RuntimeException('No event successful events found');
        }
    }

    /**
     * @param string $providerName
     * @param string $environmentName
     * @param string $template
     */
    public function sendDeploymentRequest($providerName, $environmentName, $template = 'simple-app')
    {
        $simpleAppComposeContents = file_get_contents(__DIR__.'/../fixtures/'.$template.'.yml');
        $contents = json_encode([
            'target' => [
                'environmentName' => $environmentName,
                'providerName' => $providerName,
            ],
            'specification' => [
                'dockerComposeContents' => $simpleAppComposeContents,
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
        $simpleAppComposeContents = file_get_contents(__DIR__.'/../fixtures/simple-app.yml');
        $contents = json_encode([
            'specification' => [
                'dockerComposeContents' => $simpleAppComposeContents,
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
}
