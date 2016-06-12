<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\DockerCompose\DockerComposeException;
use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\Security\Credentials\BucketContainer;

class RepositoryComponentsResolver implements ComponentsResolver
{
    /**
     * @var FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @var ProjectParser
     */
    private $projectParser;

    /**
     * @param FileSystemResolver $fileSystemResolver
     * @param ProjectParser      $projectParser
     */
    public function __construct(FileSystemResolver $fileSystemResolver, ProjectParser $projectParser)
    {
        $this->fileSystemResolver = $fileSystemResolver;
        $this->projectParser = $projectParser;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(CodeReference $codeReference, BucketContainer $bucketContainer)
    {
        $fileSystem = $this->fileSystemResolver->getFileSystem($codeReference, $bucketContainer);
        $dockerComposeComponents = [];

        try {
            foreach ($this->projectParser->parse($fileSystem, $codeReference->getBranch()) as $name => $raw) {
                $dockerComposeComponents[] = DockerComposeComponent::fromParsed($name, $raw);
            }
        } catch (DockerComposeException $e) {
            throw new ResolveException($e->getMessage(), $e->getCode(), $e);
        }

        return $dockerComposeComponents;
    }
}
