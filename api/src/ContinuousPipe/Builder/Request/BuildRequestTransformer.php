<?php

namespace ContinuousPipe\Builder\Request;

interface BuildRequestTransformer
{
    /**
     * @param BuildRequest $request
     *
     * @throws BuildRequestException
     *
     * @return BuildRequest
     */
    public function transform(BuildRequest $request) : BuildRequest;
}
