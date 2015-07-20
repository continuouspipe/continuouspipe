<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\DockerCompose\DockerComposeComponent;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubClientFactory;
use ContinuousPipe\River\CodeRepository\GitHubRelativeFileSystem;
use ContinuousPipe\River\CodeRepository\RepositoryAddressDescriptor;
use ContinuousPipe\River\Command\BuildImageCommand;
use ContinuousPipe\River\Event\TideStarted;
use League\Tactician\CommandBus;
use SimpleBus\Message\Bus\MessageBus;

class TideStartedListener
{
    /**
     * @var MessageBus
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

    public function __construct(MessageBus $commandBus, ProjectParser $dockerComposeProjectParser, GitHubClientFactory $gitHubClientFactory, RepositoryAddressDescriptor $repositoryAddressDescriptor)
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
        $codeRepository = $event->getFlow()->getRepository();
        $buildRequests = $this->createBuildRequests($codeRepository, $event->getCodeReference());
        if (empty($buildRequests)) {
            throw new \RuntimeException('No image to build');
        }

        foreach ($buildRequests as $buildRequest) {
            $command = new BuildImageCommand($event->getTideUuid(), $buildRequest);

            $this->commandBus->handle($command);
        }
    }

    /**
     * @param CodeRepository $repository
     * @param CodeReference  $codeReference
     *
     * @return \ContinuousPipe\Builder\Request\BuildRequest[]
     *
     * @throws CodeRepository\InvalidRepositoryAddress
     */
    private function createBuildRequests(CodeRepository $repository, CodeReference $codeReference)
    {
        $dockerComposeComponents = $this->dockerComposeProjectParser->parse(
            new GitHubRelativeFileSystem(
                $this->gitHubClientFactory->createAnonymous(),
                $this->repositoryAddressDescriptor->getDescription($repository->getAddress()),
                $codeReference->getReference()
            ),
            $codeReference->getReference()
        );

        $buildRequests = [];
        foreach ($dockerComposeComponents as $rawDockerComposeComponent) {
            $dockerComposeComponent = DockerComposeComponent::fromParsed($rawDockerComposeComponent);
            if (!$dockerComposeComponent->hasToBeBuilt()) {
                continue;
            }

            $imageName = $dockerComposeComponent->getImageName();
            $image = new Image($imageName, $codeReference->getReference());

            $buildRequestRepository = new Repository($repository->getAddress(), $codeReference->getReference());
            $buildRequests[] = new BuildRequest($buildRequestRepository, $image);
        }

        return $buildRequests;
    }
}
