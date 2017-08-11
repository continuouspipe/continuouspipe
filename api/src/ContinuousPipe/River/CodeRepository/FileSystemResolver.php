<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface FileSystemResolver
{
    /**
     * Get file system for the given code repository and reference.
     *
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     *
     * @return \ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem
     *
     * @throws CodeRepositoryException
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference) : RelativeFileSystem;

    /**
     * Returns true if supports the following flow.
     *
     * @param FlatFlow $flow
     *
     * @return bool
     */
    public function supports(FlatFlow $flow) : bool;
}
