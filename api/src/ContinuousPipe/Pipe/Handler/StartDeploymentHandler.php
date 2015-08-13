<?php

namespace ContinuousPipe\Pipe\Handler;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\DockerCompose\Loader\YamlLoader;
use ContinuousPipe\Pipe\Command\StartDeploymentCommand;

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
     * @param ProviderRepository $providerRepository
     * @param YamlLoader $dockerComposeYamlLoader
     * @param EnvironmentClientFactory $environmentClientFactory
     */
    public function __construct(ProviderRepository $providerRepository, YamlLoader $dockerComposeYamlLoader, EnvironmentClientFactory $environmentClientFactory)
    {
        $this->providerRepository = $providerRepository;
        $this->dockerComposeYamlLoader = $dockerComposeYamlLoader;
        $this->environmentClientFactory = $environmentClientFactory;
    }

    /**
     * @param StartDeploymentCommand $command
     */
    public function handle(StartDeploymentCommand $command)
    {
        $request = $command->getDeployment()->getRequest();
        $environment = $this->dockerComposeYamlLoader->load(
            $request->getEnvironmentName(),
            $request->getDockerComposeContents()
        );

        $provider = $this->providerRepository->find($request->getProviderName());
        $environmentClient = $this->environmentClientFactory->getByProvider($provider);
        $environmentClient->createOrUpdate($environment);
    }
}
