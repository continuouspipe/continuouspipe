<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;

class BuildRequestCreator
{
    /**
     * @var ProjectParser
     */
    private $dockerComposeProjectParser;
    /**
     * @var CodeRepository\FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @param ProjectParser                     $dockerComposeProjectParser
     * @param CodeRepository\FileSystemResolver $fileSystemResolver
     */
    public function __construct(ProjectParser $dockerComposeProjectParser, CodeRepository\FileSystemResolver $fileSystemResolver)
    {
        $this->dockerComposeProjectParser = $dockerComposeProjectParser;
        $this->fileSystemResolver = $fileSystemResolver;
    }

    /**
     * @param CodeRepository $repository
     * @param CodeReference  $codeReference
     *
     * @return \ContinuousPipe\Builder\Request\BuildRequest[]
     *
     * @throws FileNotFound
     * @throws CodeRepository\InvalidRepositoryAddress
     */
    public function createBuildRequests(CodeRepository $repository, CodeReference $codeReference)
    {
        $dockerComposeComponents = $this->dockerComposeProjectParser->parse(
            $this->fileSystemResolver->getFileSystem($repository, $codeReference),
            $codeReference->getReference()
        );

        $buildRequests = [];
        foreach ($dockerComposeComponents as $rawDockerComposeComponent) {
            $dockerComposeComponent = CodeRepository\DockerCompose\DockerComposeComponent::fromParsed($rawDockerComposeComponent);
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
