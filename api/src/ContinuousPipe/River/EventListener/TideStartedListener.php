<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeRepository\DockerCompose\DockerComposeComponent;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubClientFactory;
use ContinuousPipe\River\CodeRepository\GitHubRelativeFileSystem;
use ContinuousPipe\River\CodeRepository\RepositoryAddressDescriptor;
use ContinuousPipe\River\Command\BuildImageCommand;
use ContinuousPipe\River\Event\TideStarted;
use League\Tactician\CommandBus;

class TideStartedListener
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var ProjectParser
     */
    private $dockerComposeProjectParser;
    /**
     * @var GitHubClientFactory
     */
    private $gitHubClientFactory;
    /**
     * @var RepositoryAddressDescriptor
     */
    private $repositoryAddressDescriptor;

    public function __construct(CommandBus $commandBus, ProjectParser $dockerComposeProjectParser, GitHubClientFactory $gitHubClientFactory, RepositoryAddressDescriptor $repositoryAddressDescriptor)
    {
        $this->commandBus = $commandBus;
        $this->dockerComposeProjectParser = $dockerComposeProjectParser;
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
    }

    /**
     * @param TideStarted $event
     */
    public function notify(TideStarted $event)
    {
        $buildRequests = $this->createBuildRequests($event->getRepository());
        if (empty($buildRequests)) {
            throw new \RuntimeException('No image to build');
        }

        foreach ($buildRequests as $buildRequest) {
            $command = new BuildImageCommand($event->getTideUuid(), $buildRequest);

            $this->commandBus->handle($command);
        }
    }

    /**
     * @param Repository $repository
     * @return BuildRequest[]
     * @throws \ContinuousPipe\River\CodeRepository\InvalidRepositoryAddress
     */
    private function createBuildRequests(Repository $repository)
    {
        $dockerComposeComponents = $this->dockerComposeProjectParser->parse(
            new GitHubRelativeFileSystem(
                $this->gitHubClientFactory->createAnonymous(),
                $this->repositoryAddressDescriptor->getDescription($repository->getAddress()),
                $repository->getBranch()
            ),
            $repository->getBranch()
        );

        $buildRequests = [];
        foreach ($dockerComposeComponents as $rawDockerComposeComponent) {
            $dockerComposeComponent = DockerComposeComponent::fromParsed($rawDockerComposeComponent);
            if (!$dockerComposeComponent->hasToBeBuilt()) {
                continue;
            }

            $imageName = $dockerComposeComponent->getImageName();
            $image = new Image($imageName, $repository->getBranch());

            $buildRequests[] = new BuildRequest($repository, $image);
        }

        return $buildRequests;
    }
}
