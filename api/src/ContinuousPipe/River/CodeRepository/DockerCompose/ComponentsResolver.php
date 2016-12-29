<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface ComponentsResolver
{
    /**
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     *
     * @throws ResolveException
     *
     * @return DockerComposeComponent[]
     */
    public function resolve(FlatFlow $flow, CodeReference $codeReference);
}
