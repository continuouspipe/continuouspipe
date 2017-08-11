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
     * @throws ResolveException
     * @throws CodeRepositoryException
     *
     * @return DockerComposeComponent[]
     */
    public function resolve(FlatFlow $flow, CodeReference $codeReference);
}
