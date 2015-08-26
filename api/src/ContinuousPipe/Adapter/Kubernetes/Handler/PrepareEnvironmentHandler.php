<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\KubernetesDeploymentContext;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\ObjectMetadata;
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
     * @param KubernetesClientFactory $clientFactory
     * @param MessageBus              $eventBus
     */
    public function __construct(KubernetesClientFactory $clientFactory, MessageBus $eventBus)
    {
        $this->clientFactory = $clientFactory;
        $this->eventBus = $eventBus;
    }

    /**
     * @param PrepareEnvironmentCommand $command
     */
    public function handle(PrepareEnvironmentCommand $command)
    {
        $context = $command->getContext();
        $logger = $context->getLogger();
        $environment = $context->getEnvironment();

        $namespaceRepository = $this->clientFactory->getByProvider($context->getProvider())->getNamespaceRepository();
        $namespaceName = $environment->getIdentifier();

        if (!$namespaceRepository->exists($namespaceName)) {
            $namespace = new KubernetesNamespace(new ObjectMetadata($environment->getIdentifier()));
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
