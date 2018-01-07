<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\DockerCompose\DockerComposeException;
use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

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
    public function resolve(FlatFlow $flow, CodeReference $codeReference)
    {
        $fileSystem = $this->fileSystemResolver->getFileSystem($flow, $codeReference);

        try {
            $components = $this->projectParser->parse($fileSystem, $codeReference->getBranch());
        } catch (DockerComposeException $e) {
            throw new ResolveException($e->getMessage(), $e->getCode(), $e);
        } catch (FileNotFound $e) {
            return [];
        }

        $dockerComposeComponents = [];
        foreach ($components as $name => $raw) {
            if (!is_array($raw)) {
                continue;
            }

            $dockerComposeComponents[] = DockerComposeComponent::fromParsed($name, $raw);
        }

        return $dockerComposeComponents;
    }
}
