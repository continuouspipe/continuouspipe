<?php

namespace ContinuousPipe\River\Task\Run\DockerCompose;

use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Task\Run\RunContext;
use Symfony\Component\Yaml\Yaml;

class Reader
{
    /**
     * @var ProjectParser
     */
    private $projectParser;

    /**
     * @var FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @param ProjectParser      $projectParser
     * @param FileSystemResolver $fileSystemResolver
     */
    public function __construct(ProjectParser $projectParser, FileSystemResolver $fileSystemResolver)
    {
        $this->projectParser = $projectParser;
        $this->fileSystemResolver = $fileSystemResolver;
    }

    /**
     * @param RunContext $context
     * @throws ImageNameNotFound
     *
     * @return string
     */
    public function getImageName(RunContext $context)
    {
        $fileSystem = $this->fileSystemResolver->getFileSystem($context->getCodeReference(), $context->getUser());
        $services = $this->projectParser->parse($fileSystem, $context->getCodeReference()->getBranch());

        $serviceName = $context->getServiceName();
        if (! isset($services[$serviceName])) {
            throw new ImageNameNotFound("Service '$serviceName' does not exist in docker-compose.yml");
        }

        $service = $services[$serviceName];
        if (! isset($service['labels']['com.continuouspipe.image-name'])) {
            throw new ImageNameNotFound("Service '$serviceName' does not have 'com.continuouspipe.image-name' label");
        }

        return $service['labels']['com.continuouspipe.image-name'];
    }
}
