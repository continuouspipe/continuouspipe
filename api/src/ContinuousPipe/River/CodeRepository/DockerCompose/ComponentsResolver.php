<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface ComponentsResolver
{
    /**
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     *
     * @throws ResolveException        If something not obvious happened, that is very likely to be a user error.
     * @throws CodeRepositoryException If something wrong with the communication to the code repository.
     *
     * @return DockerComposeComponent[]
     */
    public function resolve(FlatFlow $flow, CodeReference $codeReference);
}
