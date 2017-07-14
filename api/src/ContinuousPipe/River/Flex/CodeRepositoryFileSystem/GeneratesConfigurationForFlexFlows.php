<?php

namespace ContinuousPipe\River\Flex\CodeRepositoryFileSystem;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class GeneratesConfigurationForFlexFlows implements FileSystemResolver
{
    /**
     * @var FileSystemResolver
     */
    private $decoratedFileSystemResolver;

    /**
     * @param FileSystemResolver $decoratedFileSystemResolver
     */
    public function __construct(FileSystemResolver $decoratedFileSystemResolver)
    {
        $this->decoratedFileSystemResolver = $decoratedFileSystemResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference): RelativeFileSystem
    {
        $fileSystem = $this->decoratedFileSystemResolver->getFileSystem($flow, $codeReference);

        if ($flow->isFlex()) {
            $fileSystem = new FileSystemThatWillGenerateConfiguration(
                $fileSystem,
                $flow
            );
        }

        return $fileSystem;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $this->decoratedFileSystemResolver->supports($flow);
    }
}
