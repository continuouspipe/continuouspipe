<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestException;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;

class PreviousStepsBackwardCompatibilityTransformer implements BuildRequestTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        if (!empty($request->getSteps())) {
            return $request;
        }

        return $request->withSteps([
            (new BuildStepConfiguration())
                ->withArchiveSource($request->getArchive())
                ->withImage($request->getImage())
                ->withRepository($request->getRepository())
                ->withContext($request->getContext())
                ->withEnvironment($request->getEnvironment() ?: []),
        ]);
    }
}
