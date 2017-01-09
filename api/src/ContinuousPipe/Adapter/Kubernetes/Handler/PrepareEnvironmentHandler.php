<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesDeploymentContext;
use ContinuousPipe\Adapter\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Adapter\Kubernetes\PrivateImages\SecretFactory;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Client;
use Kubernetes\Client\Exception\SecretNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\LocalObjectReference;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Model\ServiceAccount;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;
use Tolerance\Operation\Callback;
use Tolerance\Operation\Runner\CallbackOperationRunner;
use Tolerance\Operation\Runner\RetryOperationRunner;
use Tolerance\Waiter\CountLimited;
use Tolerance\Waiter\Linear;
use Tolerance\Waiter\Waiter;

class PrepareEnvironmentHandler implements DeploymentHandler
{
    /**
     * @var KubernetesClientFactory
     */
    private $kubernetesClientFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var NamingStrategy
     */
    private $namingStrategy;

    /**
     * @var SecretFactory
     */
    private $secretFactory;

    /**
     * @var Waiter
     */
    private $waiter;

    /**
     * @param KubernetesClientFactory $kubernetesClientFactory
     * @param MessageBus              $eventBus
     * @param LoggerFactory           $loggerFactory
     * @param NamingStrategy          $namingStrategy
     * @param SecretFactory           $secretFactory
     * @param Waiter                  $waiter
     */
    public function __construct(KubernetesClientFactory $kubernetesClientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, NamingStrategy $namingStrategy, SecretFactory $secretFactory, Waiter $waiter)
    {
        $this->kubernetesClientFactory = $kubernetesClientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->namingStrategy = $namingStrategy;
        $this->secretFactory = $secretFactory;
        $this->waiter = $waiter;
    }

    /**
     * @param PrepareEnvironmentCommand $command
     */
    public function handle(PrepareEnvironmentCommand $command)
    {
        $context = $command->getContext();
        $client = $this->kubernetesClientFactory->getByCluster($context->getCluster());

        try {
            $namespace = $this->createNamespaceIfNotExists($client, $context);
            $this->createOrUpdateNamespaceCredentials($client, $context, $namespace);
        } catch (\Exception $e) {
            $logger = $this->loggerFactory->from($context->getLog());
            $logger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);

            $this->eventBus->handle(new DeploymentFailed($context));

            return;
        }

        $context->add(KubernetesDeploymentContext::NAMESPACE_KEY, $namespace);
        $this->eventBus->handle(new EnvironmentPrepared($context));
    }

    /**
     * @param Client            $client
     * @param DeploymentContext $context
     *
     * @return KubernetesNamespace
     */
    private function createNamespaceIfNotExists(Client $client, DeploymentContext $context)
    {
        $environment = $context->getEnvironment();

        $namespaceRepository = $client->getNamespaceRepository();
        $namespace = $this->namingStrategy->getEnvironmentNamespace($environment);
        $namespaceName = $namespace->getMetadata()->getName();

        if (!$namespaceRepository->exists($namespaceName)) {
            $namespace = $namespaceRepository->create($namespace);

            $this->eventBus->handle(new NamespaceCreated($namespace, $context));
        } else {
            $namespace = $namespaceRepository->findOneByName($namespaceName);
        }

        return $namespace;
    }

    /**
     * @param Client              $client
     * @param DeploymentContext   $context
     * @param KubernetesNamespace $namespace
     */
    private function createOrUpdateNamespaceCredentials(Client $client, DeploymentContext $context, KubernetesNamespace $namespace)
    {
        $namespaceClient = $client->getNamespaceClient($namespace);
        $secret = $this->createOrUpdateSecret($namespaceClient, $context);

        $serviceAccountRepository = $namespaceClient->getServiceAccountRepository();

        $runner = new RetryOperationRunner(
            new CallbackOperationRunner(),
            new CountLimited(new Linear($this->waiter, 1), 10)
        );

        $runner->run(new Callback(function () use ($serviceAccountRepository, $secret) {
            $defaultServiceAccount = $serviceAccountRepository->findByName('default');

            if (!$this->alreadyHaveSecret($defaultServiceAccount, $secret)) {
                $imagePullSecrets = $defaultServiceAccount->getImagePullSecrets();
                $imagePullSecrets[] = new LocalObjectReference(
                    $secret->getMetadata()->getName()
                );

                $serviceAccountRepository->update(new ServiceAccount(
                    $defaultServiceAccount->getMetadata(),
                    $defaultServiceAccount->getSecrets(),
                    $imagePullSecrets
                ));
            }
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof Kubernetes;
    }

    /**
     * @param NamespaceClient   $namespaceClient
     * @param DeploymentContext $context
     *
     * @return \Kubernetes\Client\Model\Secret
     */
    private function createOrUpdateSecret(NamespaceClient $namespaceClient, DeploymentContext $context)
    {
        $secret = $this->secretFactory->createDockerRegistrySecret($context);
        $secretRepository = $namespaceClient->getSecretRepository();

        try {
            $existingSecret = $secretRepository->findOneByName($secret->getMetadata()->getName());

            if ($existingSecret->getData() != $secret->getData()) {
                $secret = $secretRepository->update($secret);
            }
        } catch (SecretNotFound $e) {
            $secret = $secretRepository->create($secret);
        }

        return $secret;
    }

    /**
     * @param ServiceAccount $serviceAccount
     * @param Secret         $secret
     *
     * @return bool
     */
    private function alreadyHaveSecret(ServiceAccount $serviceAccount, Secret $secret)
    {
        foreach ($serviceAccount->getImagePullSecrets() as $reference) {
            if ($reference->getName() == $secret->getMetadata()->getName()) {
                return true;
            }
        }

        return false;
    }
}
