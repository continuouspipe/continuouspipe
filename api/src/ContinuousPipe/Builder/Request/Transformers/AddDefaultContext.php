<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;

class AddDefaultContext implements BuildRequestTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        return $request->withSteps(array_map(function (BuildStepConfiguration $step) use ($request) {
            if (null !== $step->getContext()) {
                return $step;
            }

            return $step->withContext(new Context('./Dockerfile', '.'));
        }, $request->getSteps()));
    }
}
