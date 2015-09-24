<?php

namespace ContinuousPipe\Pipe\Handler;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\AdapterProviderRepository;
use ContinuousPipe\Pipe\Command\StartDeploymentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentStarted;
use ContinuousPipe\Pipe\Logging\DeploymentLoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class StartDeploymentHandler
{
    /**
     * @var AdapterProviderRepository
     */
    private $providerRepository;

    /**
     * @var EnvironmentClientFactory
     */
    private $environmentClientFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var DeploymentLoggerFactory
     */
    private $loggerFactory;

    /**
     * @param AdapterProviderRepository $providerRepository
     * @param EnvironmentClientFactory  $environmentClientFactory
     * @param MessageBus                $eventBus
     * @param DeploymentLoggerFactory   $loggerFactory
     */
    public function __construct(AdapterProviderRepository $providerRepository, EnvironmentClientFactory $environmentClientFactory, MessageBus $eventBus, DeploymentLoggerFactory $loggerFactory)
    {
        $this->providerRepository = $providerRepository;
        $this->environmentClientFactory = $environmentClientFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param StartDeploymentCommand $command
     */
    public function handle(StartDeploymentCommand $command)
    {
        $deployment = $command->getDeployment();

        $logger = $this->loggerFactory->create($deployment);
        $logger->start();

        $request = $deployment->getRequest();
        $target = $request->getTarget();
        $specification = $request->getSpecification();

        $logger->append(new Text(sprintf(
            'Deploying to the environment "%s" to provider "%s"',
            $target->getEnvironmentName(),
            $target->getProviderName()
        )));

        $environment = new Environment(
            $target->getEnvironmentName(),
            $target->getEnvironmentName(),
            $specification->getComponents()
        );

        $logger->append(new Text(sprintf(
            'Found %d components in `docker-compose.yml` file.',
            count($environment->getComponents())
        )));

        list($type, $name) = explode('/', $target->getProviderName());
        $provider = $this->providerRepository->findByTypeAndIdentifier($type, $name);
        $deploymentContext = new DeploymentContext($deployment, $provider, $logger->getLog(), $environment);
        $this->eventBus->handle(new DeploymentStarted($deploymentContext));
    }
}
