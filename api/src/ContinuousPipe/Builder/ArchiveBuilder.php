<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

interface ArchiveBuilder
{
    /**
     * @param BuildStepConfiguration $buildStepConfiguration
     *
     * @throws ArchiveCreationException
     *
     * @return Archive
     */
    public function createArchive(BuildStepConfiguration $buildStepConfiguration) : Archive;

    /**
     * Returns true if the builder supports the build request.
     *
     * @param BuildStepConfiguration $buildStepConfiguration
     *
     * @return bool
     */
    public function supports(BuildStepConfiguration $buildStepConfiguration) : bool;
}
