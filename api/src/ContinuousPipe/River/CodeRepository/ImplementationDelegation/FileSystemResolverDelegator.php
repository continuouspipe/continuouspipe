<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class FileSystemResolverDelegator implements FileSystemResolver
{
    /**
     * @var array|FileSystemResolver[]
     */
    private $fileSystemResolvers;

    /**
     * @param FileSystemResolver[] $fileSystemResolvers
     */
    public function __construct(array $fileSystemResolvers)
    {
        $this->fileSystemResolvers = $fileSystemResolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference) : RelativeFileSystem
    {
        foreach ($this->fileSystemResolvers as $fileSystemResolver) {
            if ($fileSystemResolver->supports($flow)) {
                return $fileSystemResolver->getFileSystem($flow, $codeReference);
            }
        }

        throw new CodeRepositoryException('No file system resolver supports the given flow');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow) : bool
    {
        foreach ($this->fileSystemResolvers as $fileSystemResolver) {
            if ($fileSystemResolver->supports($flow)) {
                return true;
            }
        }

        return false;
    }
}
