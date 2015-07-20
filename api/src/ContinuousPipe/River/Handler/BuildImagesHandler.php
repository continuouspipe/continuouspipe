<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\BuildImagesCommand;
use ContinuousPipe\River\Event\ImageBuildsStarted;
use ContinuousPipe\River\Repository\TideRepository;
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
use SimpleBus\Message\Bus\MessageBus;

class BuildImagesHandler
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
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(MessageBus $commandBus, MessageBus $eventBus, ProjectParser $dockerComposeProjectParser, GitHubClientFactory $gitHubClientFactory, RepositoryAddressDescriptor $repositoryAddressDescriptor, TideRepository $tideRepository)
    {
        $this->commandBus = $commandBus;
        $this->dockerComposeProjectParser = $dockerComposeProjectParser;
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
        $this->tideRepository = $tideRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * @param BuildImagesCommand $command
     */
    public function handle(BuildImagesCommand $command)
    {
        $tideUuid = $command->getTideUuid();
        $tide = $this->tideRepository->find($tideUuid);

        $codeRepository = $tide->getCodeRepository();
        $buildRequests = $this->createBuildRequests($codeRepository, $tide->getCodeReference());
        if (empty($buildRequests)) {
            throw new \RuntimeException('No image to build');
        }

        $this->eventBus->handle(new ImageBuildsStarted($tideUuid, $buildRequests));

        foreach ($buildRequests as $buildRequest) {
            $command = new BuildImageCommand($tideUuid, $buildRequest);
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
