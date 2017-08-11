<?php

namespace ContinuousPipe\River\CodeRepository\FileSystem;

use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class PartiallyOverwrittenFileSystemResolver implements FileSystemResolver
{
    /**
     * @var FileSystemResolver
     */
    private $decoratedResolver;

    /**
     * @var array
     */
    private $overrideRules = [];

    /**
     * @param FileSystemResolver $decoratedResolver
     */
    public function __construct(FileSystemResolver $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference): RelativeFileSystem
    {
        return new PartiallyOverwrittenFileSystem(
            $this->decoratedResolver->getFileSystem($flow, $codeReference),
            $this->overrideRules
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $this->decoratedResolver->supports($flow);
    }

    public function overrideFile(string $path, string $contents)
    {
        $this->overrideRules[$path] = $contents;
    }
}
