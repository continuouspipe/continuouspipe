<?php

namespace ContinuousPipe\Pipe\Handler;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\DockerCompose\Loader\YamlLoader;
use ContinuousPipe\Pipe\Command\StartDeploymentCommand;
use ContinuousPipe\Pipe\Deployment;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\DeploymentSuccessful;
use ContinuousPipe\Pipe\Logging\DeploymentLoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class StartDeploymentHandler
{
    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @var YamlLoader
     */
    private $dockerComposeYamlLoader;

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
     * @param ProviderRepository $providerRepository
     * @param YamlLoader $dockerComposeYamlLoader
     * @param EnvironmentClientFactory $environmentClientFactory
     * @param MessageBus $eventBus
     * @param DeploymentLoggerFactory $loggerFactory
     */
    public function __construct(ProviderRepository $providerRepository, YamlLoader $dockerComposeYamlLoader, EnvironmentClientFactory $environmentClientFactory, MessageBus $eventBus, DeploymentLoggerFactory $loggerFactory)
    {
        $this->providerRepository = $providerRepository;
        $this->dockerComposeYamlLoader = $dockerComposeYamlLoader;
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
        $deployment->updateStatus(Deployment::STATUS_RUNNING);

        $logger = $this->loggerFactory->create($deployment);
        $logger->start();

        try {

            $request = $deployment->getRequest();
            $logger->append(new Text(sprintf(
                'Deploying to the environment "%s" to provider "%s"',
                $request->getEnvironmentName(),
                $request->getProviderName()
            )));

            $environment = $this->dockerComposeYamlLoader->load(
                $request->getEnvironmentName(),
                $request->getDockerComposeContents()
            );

            $logger->append(new Text(sprintf(
                'Found %d components in `docker-compose.yml` file.',
                count($environment->getComponents())
            )));

            $provider = $this->providerRepository->find($request->getProviderName());
            $environmentClient = $this->environmentClientFactory->getByProvider($provider);
            $environmentClient->createOrUpdate($environment, $logger);

            $deployment->updateStatus(Deployment::STATUS_SUCCESS);
            $this->eventBus->handle(new DeploymentSuccessful($deployment));
        } catch (\Exception $e) {
            $deployment->updateStatus(Deployment::STATUS_FAILURE);
            $this->eventBus->handle(new DeploymentFailed($deployment));
        }
    }
}
