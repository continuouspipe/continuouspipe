<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\User\User;

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
     * @param User           $user
     *
     * @return \ContinuousPipe\Builder\Request\BuildRequest[]
     *
     * @throws FileNotFound
     * @throws CodeRepository\InvalidRepositoryAddress
     */
    public function createBuildRequests(CodeRepository $repository, CodeReference $codeReference, User $user)
    {
        $dockerComposeComponents = $this->dockerComposeProjectParser->parse(
            $this->fileSystemResolver->getFileSystem($repository, $codeReference, $user),
            $codeReference->getReference()
        );

        $buildRequests = [];
        foreach ($dockerComposeComponents as $componentName => $rawDockerComposeComponent) {
            $dockerComposeComponent = CodeRepository\DockerCompose\DockerComposeComponent::fromParsed($rawDockerComposeComponent);
            if (!$dockerComposeComponent->hasToBeBuilt()) {
                continue;
            }

            try {
                $imageName = $dockerComposeComponent->getImageName();
            } catch (CodeRepository\DockerCompose\ResolveException $e) {
                throw new BuilderException(sprintf('Unable to resolve image name of component "%s": %s', $componentName, $e->getMessage()));
            }

            $image = new Image($imageName, $codeReference->getReference());

            $buildRequestRepository = new Repository($repository->getAddress(), $codeReference->getReference());
            $buildRequests[] = new BuildRequest($buildRequestRepository, $image);
        }

        return $buildRequests;
    }
}
