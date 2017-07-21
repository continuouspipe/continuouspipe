<?php

namespace ContinuousPipe\River\Flex;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class FlexAvailabilityDetector
{
    /**
     * @var FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @param FileSystemResolver $fileSystemResolver
     */
    public function __construct(FileSystemResolver $fileSystemResolver)
    {
        $this->fileSystemResolver = $fileSystemResolver;
    }

    /**
     * @param FlatFlow $flow
     *
     * @throws FlexException
     *
     * @return bool
     */
    public function isFlexAvailable(FlatFlow $flow) : bool
    {
        $codeReference = CodeReference::repositoryDefault($flow->getRepository());

        try {
            $fileSystem = $this->fileSystemResolver->getFileSystem($flow, $codeReference);
        } catch (CodeRepositoryException $e) {
            throw new FlexException('Cannot access the code repository', $e->getCode(), $e);
        }

        try {
            $composerFile = $fileSystem->getContents('composer.json');
        } catch (FileNotFound $e) {
            throw new FlexException('File `composer.json` not found in the repository', $e->getCode(), $e);
        }

        try {
            $composer = \GuzzleHttp\json_decode($composerFile, true);
        } catch (\InvalidArgumentException $e) {
            throw new FlexException('File `composer.json` is not a valid JSON file');
        }

        if (!isset($composer['require']) || !isset($composer['require']['symfony/flex'])) {
            throw new FlexException('`symfony/flex` is not a dependency of your project');
        }

        return true;
    }
}
