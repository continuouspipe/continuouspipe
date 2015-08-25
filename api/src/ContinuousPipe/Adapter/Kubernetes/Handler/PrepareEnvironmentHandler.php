<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Adapter\Kubernetes\KubernetesDeploymentContext;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\ObjectMetadata;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class PrepareEnvironmentHandler
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
        if (!$this->shouldHandle($context)) {
            return;
        }

        $logger = $context->getLogger();
        $environment = $context->getEnvironment();

        $client = $this->clientFactory->getByProvider($context->getProvider());
        $namespaceRepository = $client->getNamespaceRepository();
        $namespaceName = $environment->getIdentifier();

        try {
            $namespace = $namespaceRepository->findOneByName($namespaceName);
            $logger->append(new Text(sprintf('Reusing existing namespace "%s"', $namespaceName)));
        } catch (NamespaceNotFound $e) {
            $namespace = new KubernetesNamespace(new ObjectMetadata($environment->getIdentifier()));
            $namespace = $client->getNamespaceRepository()->create($namespace);
            $logger->append(new Text(sprintf('Created new namespace "%s"', $namespaceName)));

            $this->eventBus->handle(new NamespaceCreated($namespace, $context));
        }

        $context->add(KubernetesDeploymentContext::NAMESPACE_KEY, $namespace);

        $this->eventBus->handle(new EnvironmentPrepared($context));
    }

    /**
     * @param DeploymentContext $context
     *
     * @return bool
     */
    private function shouldHandle(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }
}
