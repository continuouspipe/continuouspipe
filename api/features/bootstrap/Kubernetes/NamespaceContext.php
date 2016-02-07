<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\PrivateImages\SecretFactory;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\HookableServiceAccountRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\InMemoryServiceAccountRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableNamespaceRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableSecretRepository;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableServiceAccountRepository;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\Pipe\Tests\MessageBus\TraceableMessageBus;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\User\User;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Exception\ServiceAccountNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\LocalObjectReference;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Model\ServiceAccount;
use LogStream\LoggerFactory;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class NamespaceContext implements Context, SnippetAcceptingContext
{
    /**
     * @var \EnvironmentContext
     */
    private $environmentContext;

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
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var InMemoryAuthenticatorClient
     */
    private $inMemoryAuthenticatorClient;
    /**
     * @var InMemoryServiceAccountRepository
     */
    private $inMemoryServiceAccountRepository;
    /**
     * @var HookableServiceAccountRepository
     */
    private $hookableServiceAccountRepository;

    /**
     * @param TraceableNamespaceRepository $namespaceRepository
     * @param TraceableMessageBus $eventBus
     * @param TraceableSecretRepository $secretRepository
     * @param TraceableServiceAccountRepository $serviceAccountRepository
     * @param LoggerFactory $loggerFactory
     * @param InMemoryAuthenticatorClient $inMemoryAuthenticatorClient
     * @param InMemoryServiceAccountRepository $inMemoryServiceAccountRepository
     * @param HookableServiceAccountRepository $hookableServiceAccountRepository
     */
    public function __construct(
        TraceableNamespaceRepository $namespaceRepository,
        TraceableMessageBus $eventBus,
        TraceableSecretRepository $secretRepository,
        TraceableServiceAccountRepository $serviceAccountRepository,
        LoggerFactory $loggerFactory,
        InMemoryAuthenticatorClient $inMemoryAuthenticatorClient,
        InMemoryServiceAccountRepository $inMemoryServiceAccountRepository,
        HookableServiceAccountRepository $hookableServiceAccountRepository
    )
    {
        $this->namespaceRepository = $namespaceRepository;
        $this->eventBus = $eventBus;
        $this->secretRepository = $secretRepository;
        $this->serviceAccountRepository = $serviceAccountRepository;
        $this->loggerFactory = $loggerFactory;
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
        $this->inMemoryServiceAccountRepository = $inMemoryServiceAccountRepository;
        $this->hookableServiceAccountRepository = $hookableServiceAccountRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->environmentContext = $scope->getEnvironment()->getContext('EnvironmentContext');
    }

    /**
     * @Then it should create a new namespace
     */
    public function itShouldCreateANewNamespace()
    {
        $numberOfCreatedNamespaces = count($this->namespaceRepository->getCreated());

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
     * @Given the service account :name to not contain any docker registry pull secret
     */
    public function theServiceAccountToNotContainAnyDockerRegistryPullSecret($name)
    {
        try {
            $serviceAccount = $this->serviceAccountRepository->findByName($name);

            $this->serviceAccountRepository->update(new ServiceAccount(
                $serviceAccount->getMetadata(),
                $serviceAccount->getSecrets(),
                []
            ));
        } catch (ServiceAccountNotFound $e) {
            $this->serviceAccountRepository->create(new ServiceAccount(
                new ObjectMetadata($name),
                [],
                []
            ));
        }
    }

    /**
     * @Given the default service account won't be created at the same time than the namespace
     */
    public function theDefaultServiceAccountWonTBeCreatedAtTheSameTimeThanTheNamespace()
    {
        $calls = 0;
        $this->hookableServiceAccountRepository->addFindByNameHook(function(ServiceAccount $serviceAccount) use (&$calls) {
            if ($calls++ < 2) {
                throw new ServiceAccountNotFound('Service account not found');
            }

            return $serviceAccount;
        });
    }

    /**
     * @Then it should not create any namespace
     */
    public function itShouldNotCreateAnyNamespace()
    {
        $numberOfCreatedNamespaces = count($this->namespaceRepository->getCreated());

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
        $namespaceCreatedEvents = array_filter($this->eventBus->getMessages(), function ($message) {
            return $message instanceof NamespaceCreated;
        });

        if (count($namespaceCreatedEvents) == 0) {
            throw new \RuntimeException('Expected to found a namespace created event, found 0');
        }
    }

    /**
     * @Then a docker registry secret should be created
     */
    public function aDockerRegistrySecretShouldBeCreated()
    {
        $matchingCreated = array_filter($this->secretRepository->getCreated(), function (Secret $secret) {
            return $this->isPrivateSecretName($secret->getMetadata()->getName());
        });

        if (count($matchingCreated) == 0) {
            throw new \RuntimeException('No docker registry secret found');
        }
    }

    /**
     * @Then the service account should be updated with a docker registry pull secret
     */
    public function theServiceAccountShouldBeUpdatedWithADockerRegistryPullSecret()
    {
        $matchingServiceAccounts = array_filter($this->serviceAccountRepository->getUpdated(), function (ServiceAccount $serviceAccount) {
            $matchingImagePulls = array_filter($serviceAccount->getImagePullSecrets(), function (LocalObjectReference $objectReference) {
                return $this->isPrivateSecretName($objectReference->getName());
            });

            return count($matchingImagePulls) > 0;
        });

        if (count($matchingServiceAccounts) == 0) {
            throw new \RuntimeException('No updated service account with docker registry pull secret found');
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function isPrivateSecretName($name)
    {
        return substr($name, 0, strlen(SecretFactory::SECRET_PREFIX)) == SecretFactory::SECRET_PREFIX;
    }

    /**
     * @Then the secret :name should be created
     */
    public function theSecretShouldBeCreated($name)
    {
        $matchingSecrets = array_filter($this->secretRepository->getCreated(), function(Secret $secret) use ($name) {
            return $secret->getMetadata()->getName() == $name;
        });

        if (count($matchingSecrets) == 0) {
            throw new \RuntimeException(sprintf(
                'No secret named "%s" found is list of created secrets',
                $name
            ));
        }
    }

    /**
     * @Then the namespace :name should be deleted
     */
    public function theNamespaceShouldBeDeleted($name)
    {
        $matchingNamespaces = array_filter($this->namespaceRepository->getDeleted(), function(KubernetesNamespace $namespace) use ($name) {
            return $namespace->getMetadata()->getName() == $name;
        });

        if (count($matchingNamespaces) == 0) {
            throw new \RuntimeException(sprintf(
                'No namespace named "%s" found is list of deleted namespaces',
                $name
            ));
        }
    }
}
