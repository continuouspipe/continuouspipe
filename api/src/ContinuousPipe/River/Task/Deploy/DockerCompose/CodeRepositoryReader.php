<?php

namespace ContinuousPipe\River\Task\Deploy\DockerCompose;

use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\CodeRepository;
use Symfony\Component\Yaml\Yaml;

class CodeRepositoryReader implements DockerComposeReader
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
     * @var DockerComposeTransformer[]
     */
    private $transformers;

    /**
     * @param ProjectParser $dockerComposeProjectParser
     * @param CodeRepository\FileSystemResolver $fileSystemResolver
     * @param DockerComposeTransformer[] $transformers
     */
    public function __construct(ProjectParser $dockerComposeProjectParser, CodeRepository\FileSystemResolver $fileSystemResolver, array $transformers = [])
    {
        $this->dockerComposeProjectParser = $dockerComposeProjectParser;
        $this->fileSystemResolver = $fileSystemResolver;
        $this->transformers = $transformers;
    }

    /**
     * Parse `docker-compose.yml` file in a final one, usable as it
     * with the built image names.
     *
     * @param DeployContext $context
     * @return string
     */
    public function getContents(DeployContext $context)
    {
        $fileSystem = $this->fileSystemResolver->getFileSystem($context->getCodeReference(), $context->getUser());
        $dockerComposeComponents = $this->dockerComposeProjectParser->parse($fileSystem, $context->getCodeReference()->getBranch());

        foreach ($this->transformers as $transformer) {
            $dockerComposeComponents = $transformer->transform($context, $dockerComposeComponents);
        }

        return Yaml::dump($dockerComposeComponents);
    }
}
