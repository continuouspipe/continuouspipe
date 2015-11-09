<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\KubernetesDeploymentContext;
use ContinuousPipe\Adapter\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Exception\ClientError;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

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
     * @param KubernetesClientFactory $kubernetesClientFactory
     * @param MessageBus              $eventBus
     * @param LoggerFactory           $loggerFactory
     * @param NamingStrategy          $namingStrategy
     */
    public function __construct(KubernetesClientFactory $kubernetesClientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, NamingStrategy $namingStrategy)
    {
        $this->kubernetesClientFactory = $kubernetesClientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param PrepareEnvironmentCommand $command
     */
    public function handle(PrepareEnvironmentCommand $command)
    {
        $context = $command->getContext();

        try {
            $namespace = $this->createNamespaceIfNotExists($context);
        } catch (ClientError $e) {
            $logger = $this->loggerFactory->from($context->getLog());
            $logger->append(new Text($e->getMessage()));

            $this->eventBus->handle(new DeploymentFailed($context));

            return;
        }

        $context->add(KubernetesDeploymentContext::NAMESPACE_KEY, $namespace);
        $this->eventBus->handle(new EnvironmentPrepared($context));
    }

    /**
     * @param DeploymentContext $context
     *
     * @return \Kubernetes\Client\Model\KubernetesNamespace
     */
    private function createNamespaceIfNotExists(DeploymentContext $context)
    {
        $logger = $this->loggerFactory->from($context->getLog());
        $environment = $context->getEnvironment();

        $namespaceRepository = $this->kubernetesClientFactory->getByProvider($context->getProvider())->getNamespaceRepository();
        $namespace = $this->namingStrategy->getEnvironmentNamespace($environment);
        $namespaceName = $namespace->getMetadata()->getName();

        if (!$namespaceRepository->exists($namespaceName)) {
            $namespace = $namespaceRepository->create($namespace);
            $logger->append(new Text(sprintf('Created new namespace "%s"', $namespaceName)));

            $this->eventBus->handle(new NamespaceCreated($namespace, $context));
        } else {
            $namespace = $namespaceRepository->findOneByName($namespaceName);
            $logger->append(new Text(sprintf('Reusing existing namespace "%s"', $namespaceName)));
        }

        return $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof Kubernetes;
    }
}
