<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\Security\Credentials\BucketContainer;

interface ComponentsResolver
{
    /**
     * Get the components of the given code reference.
     *
     * @param CodeReference   $codeReference
     * @param BucketContainer $bucketContainer
     *
     * @throws ResolveException
     *
     * @return DockerComposeComponent[]
     */
    public function resolve(CodeReference $codeReference, BucketContainer $bucketContainer);
}
