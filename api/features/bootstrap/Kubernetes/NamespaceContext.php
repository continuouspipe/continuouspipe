<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesProvider;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableNamespaceRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableSecretRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableServiceAccountRepository;
use ContinuousPipe\Pipe\Deployment;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Tests\FakeProvider;
use ContinuousPipe\Pipe\Tests\MessageBus\TraceableMessageBus;
use ContinuousPipe\User\User;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\LocalObjectReference;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Model\ServiceAccount;
use LogStream\EmptyLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class NamespaceContext implements Context, SnippetAcceptingContext
{
    /**
     * @var \EnvironmentContext
     */
    private $environmentContext;

    /**
     * @var \Kubernetes\ProviderContext
     */
    private $providerContext;

    /**
     * @var TraceableNamespaceRepository
     */
    private $namespaceRepository;

    /**
     * @var TraceableMessageBus
     */
    private $eventBus;

    /**
     * @var TraceableSecretRepository
     */
    private $secretRepository;

    /**
     * @var TraceableServiceAccountRepository
     */
    private $serviceAccountRepository;

    /**
     * @param TraceableNamespaceRepository $namespaceRepository
     * @param TraceableMessageBus $eventBus
     * @param TraceableSecretRepository $secretRepository
     * @param TraceableServiceAccountRepository $serviceAccountRepository
     */
    public function __construct(TraceableNamespaceRepository $namespaceRepository, TraceableMessageBus $eventBus, TraceableSecretRepository $secretRepository, TraceableServiceAccountRepository $serviceAccountRepository)
    {
        $this->namespaceRepository = $namespaceRepository;
        $this->eventBus = $eventBus;
        $this->secretRepository = $secretRepository;
        $this->serviceAccountRepository = $serviceAccountRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->environmentContext = $scope->getEnvironment()->getContext('EnvironmentContext');
        $this->providerContext = $scope->getEnvironment()->getContext('Kubernetes\ProviderContext');
    }

    /**
     * @When I send a deployment request for a non-existing environment
     */
    public function iSendADeploymentRequestForANonExistingEnvironment()
    {
        $this->environmentContext->sendDeploymentRequest('kubernetes/'.ProviderContext::DEFAULT_PROVIDER_NAME, 'non-existing');
    }

    /**
     * @When I send a deployment request from application template :template
     */
    public function iSendADeploymentRequestFromApplicationTemplate($template)
    {
        $this->iHaveANamespace('existing');
        $this->environmentContext->sendDeploymentRequest('kubernetes/'.ProviderContext::DEFAULT_PROVIDER_NAME, 'existing', $template);
    }

    /**
     * @Then it should create a new namespace
     */
    public function itShouldCreateANewNamespace()
    {
        $numberOfCreatedNamespaces = count($this->namespaceRepository->getCreatedRepositories());

        if ($numberOfCreatedNamespaces == 0) {
            throw new \RuntimeException('No namespace were created');
        }
    }

    /**
     * @Given I have a namespace :name
     */
    public function iHaveANamespace($name)
    {
        try {
            $namespace = $this->namespaceRepository->findOneByName($name);
        } catch (NamespaceNotFound $e) {
            $namespace = $this->namespaceRepository->create(new KubernetesNamespace(new ObjectMetadata($name)));
            $this->namespaceRepository->clear();
        }

        return $namespace;
    }

    /**
     * @When I send a deployment request for the environment :environmentName
     */
    public function iSendADeploymentRequestForTheEnvironment($environmentName)
    {
        $this->environmentContext->sendDeploymentRequest('kubernetes/'.ProviderContext::DEFAULT_PROVIDER_NAME, $environmentName);
    }

    /**
     * @Then it should reuse this namespace
     */
    public function itShouldReuseThisNamespace()
    {
        $numberOfCreatedNamespaces = count($this->namespaceRepository->getCreatedRepositories());

        if ($numberOfCreatedNamespaces !== 0) {
            throw new \RuntimeException(sprintf(
                'Expected 0 namespace to be created, got %d',
                $numberOfCreatedNamespaces
            ));
        }
    }

    /**
     * @Then it should dispatch the namespace created event
     */
    public function itShouldDispatchTheNamespaceCreatedEvent()
    {
        $namespaceCreatedEvents = array_filter($this->eventBus->getMessages(), function($message) {
            return $message instanceof NamespaceCreated;
        });

        if (count($namespaceCreatedEvents) == 0) {
            throw new \RuntimeException('Expected to found a namespace created event, found 0');
        }
    }

    /**
     * @When a namespace is created
     */
    public function aNamespaceIsCreated()
    {
        $this->eventBus->handle(new NamespaceCreated(
            new KubernetesNamespace(new ObjectMetadata('foo')),
            new DeploymentContext(
                Deployment::fromRequest(
                    new DeploymentRequest(),
                    new User('samuel')
                ),
                $this->providerContext->iHaveAValidKubernetesProvider(),
                new EmptyLogger()
            )
        ));
    }

    /**
     * @Then the secret :name should be created
     */
    public function theSecretShouldBeCreated($name)
    {
        $matchingCreated = array_filter($this->secretRepository->getCreated(), function(Secret $secret) use ($name) {
            return $secret->getMetadata()->getName() == $name;
        });

        if (count($matchingCreated) == 0) {
            throw new \RuntimeException(sprintf('No secret named "%s" found', $name));
        }
    }

    /**
     * @Then the service account should be updated with a pull secret :name
     */
    public function theServiceAccountShouldBeUpdatedWithAPullSecret($name)
    {
        $matchingServiceAccounts = array_filter($this->serviceAccountRepository->getUpdated(), function(ServiceAccount $serviceAccount) use ($name) {
            $matchingImagePulls = array_filter($serviceAccount->getImagePullSecrets(), function(LocalObjectReference $objectReference) use ($name) {
                return $objectReference->getName() == $name;
            });

            return count($matchingImagePulls) > 0;
        });

        if (count($matchingServiceAccounts) == 0) {
            throw new \RuntimeException(sprintf('No updated service account named "%s" found', $name));
        }
    }
}
