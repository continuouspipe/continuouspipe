<?php

namespace ContinuousPipe\Pipe\Handler;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\DockerCompose\Loader\YamlLoader;
use ContinuousPipe\Pipe\Command\StartDeploymentCommand;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\DeploymentSuccessful;
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
     * @param ProviderRepository $providerRepository
     * @param YamlLoader $dockerComposeYamlLoader
     * @param EnvironmentClientFactory $environmentClientFactory
     * @param MessageBus $eventBus
     */
    public function __construct(ProviderRepository $providerRepository, YamlLoader $dockerComposeYamlLoader, EnvironmentClientFactory $environmentClientFactory, MessageBus $eventBus)
    {
        $this->providerRepository = $providerRepository;
        $this->dockerComposeYamlLoader = $dockerComposeYamlLoader;
        $this->environmentClientFactory = $environmentClientFactory;
        $this->eventBus = $eventBus;
    }

    /**
     * @param StartDeploymentCommand $command
     */
    public function handle(StartDeploymentCommand $command)
    {
        try {
            $request = $command->getDeployment()->getRequest();
            $environment = $this->dockerComposeYamlLoader->load(
                $request->getEnvironmentName(),
                $request->getDockerComposeContents()
            );

            $provider = $this->providerRepository->find($request->getProviderName());
            $environmentClient = $this->environmentClientFactory->getByProvider($provider);
            $environmentClient->createOrUpdate($environment);

            $this->eventBus->handle(new DeploymentSuccessful($command->getDeployment()));
        } catch (\Exception $e) {
            $this->eventBus->handle(new DeploymentFailed($command->getDeployment()));
        }
    }
}
