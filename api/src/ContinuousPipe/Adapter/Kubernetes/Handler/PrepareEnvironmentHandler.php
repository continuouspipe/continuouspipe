<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\KubernetesDeploymentContext;
use ContinuousPipe\Adapter\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class PrepareEnvironmentHandler implements DeploymentHandler
{
    /**
     * @var KubernetesClientFactory
     */
    private $clientFactory;

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
     * @param KubernetesClientFactory $clientFactory
     * @param MessageBus              $eventBus
     * @param LoggerFactory           $loggerFactory
     * @param NamingStrategy          $namingStrategy
     */
    public function __construct(KubernetesClientFactory $clientFactory, MessageBus $eventBus, LoggerFactory $loggerFactory, NamingStrategy $namingStrategy)
    {
        $this->clientFactory = $clientFactory;
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
        $logger = $this->loggerFactory->from($context->getLog());
        $environment = $context->getEnvironment();

        $namespaceRepository = $this->clientFactory->getByProvider($context->getProvider())->getNamespaceRepository();
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

        $context->add(KubernetesDeploymentContext::NAMESPACE_KEY, $namespace);

        $this->eventBus->handle(new EnvironmentPrepared($context));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }
}
