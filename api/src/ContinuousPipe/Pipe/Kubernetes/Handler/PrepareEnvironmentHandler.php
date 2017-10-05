<?php

namespace ContinuousPipe\Pipe\Kubernetes\Handler;

use ContinuousPipe\Pipe\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Pipe\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Pipe\Kubernetes\KubernetesDeploymentContext;
use ContinuousPipe\Pipe\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Pipe\Kubernetes\PrivateImages\SecretFactory;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Client;
use Kubernetes\Client\Exception\ObjectNotFound;
use Kubernetes\Client\Exception\SecretNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\LocalObjectReference;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\RBAC\RoleBinding;
use Kubernetes\Client\Model\RBAC\RoleRef;
use Kubernetes\Client\Model\RBAC\Subject;
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

        try {
            $cluster = $context->getCluster();
            $client = $this->kubernetesClientFactory->getByCluster($cluster);

            $namespace = $this->createNamespaceIfNotExists($client, $context);
            $this->createOrUpdateNamespaceCredentials($client, $context, $namespace);

            if (null !== ($rbacConfiguration = $this->clusterPolicyConfiguration($cluster, 'rbac'))) {
                $this->createOrUpdateRbacBinding($client->getNamespaceClient($namespace), $cluster, $rbacConfiguration);
            }
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

    private function createOrUpdateRbacBinding(NamespaceClient $namespaceClient, Kubernetes $cluster, array $rbacConfiguration)
    {
        if (!isset($rbacConfiguration['cluster-role'])) {
            throw new \InvalidArgumentException('Configuration "cluster-role" not found in RBAC configuration');
        }

        $bindingName = 'team-service-account-is-managed-used';

        try {
            $namespaceClient->getRoleBindingRepository()->findOneByName($bindingName);
        } catch (ObjectNotFound $e) {
            $namespaceClient->getRoleBindingRepository()->create(new RoleBinding(
                new ObjectMetadata('team-service-account-is-managed-used'),
                new RoleRef(
                    'rbac.authorization.k8s.io',
                    'ClusterRole',
                    $rbacConfiguration['cluster-role']
                ),
                [
                    new Subject(
                        'User',
                        $this->usernameFromClusterCredentials($cluster)
                    )
                ]
            ));
        }
    }

    private function usernameFromClusterCredentials(Kubernetes $cluster)
    {
        if (null !== ($serviceAccount = $cluster->getCredentials()->getGoogleCloudServiceAccount())) {
            $username = $this->usernameFromServiceAccount($serviceAccount);
        } elseif (null === ($username = $cluster->getCredentials()->getUsername())) {
            throw new \InvalidArgumentException('Can\'t get username to create role binding for, from cluster\'s credentials');
        }

        return $username;
    }

    private function usernameFromServiceAccount(string $serviceAccountAsBase64) : string
    {
        try {
            $serviceAccountJson = \GuzzleHttp\json_decode(base64_decode($serviceAccountAsBase64), true);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Service account is not valid', $e->getCode(), $e);
        }

        if (!isset($serviceAccountJson['client_email'])) {
            throw new \InvalidArgumentException('Service account do not contain the `client_email` key');
        }

        return $serviceAccountJson['client_email'];
    }

    /**
     * @param Cluster $cluster
     * @param string $policyName
     *
     * @return array|null
     */
    private function clusterPolicyConfiguration(Cluster $cluster, string $policyName)
    {
        foreach ($cluster->getPolicies() as $policy) {
            if ($policy->getName() == $policyName) {
                return $policy->getConfiguration();
            }
        }

        return null;
    }
}
