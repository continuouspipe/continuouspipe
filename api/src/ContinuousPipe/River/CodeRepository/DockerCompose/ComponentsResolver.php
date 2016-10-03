<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\Security\Credentials\BucketContainer;

interface ComponentsResolver
{
    /**
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @throws ResolveException
     *
     * @return DockerComposeComponent[]
     */
    public function resolve(Flow $flow, CodeReference $codeReference);

    /**
     * Get the components of the given code reference.
     *
     * @param CodeReference   $codeReference
     * @param BucketContainer $bucketContainer
     *
     * @throws ResolveException
     *
     * @deprecated This method is deprecated in favor of the `resolve` method, as this should be
     *             always in the context of a flow
     *
     * @return DockerComposeComponent[]
     */
    public function resolveByCodeReferenceAndBucket(CodeReference $codeReference, BucketContainer $bucketContainer);
}
