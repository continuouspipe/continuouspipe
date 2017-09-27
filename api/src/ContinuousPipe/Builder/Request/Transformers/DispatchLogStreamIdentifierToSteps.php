<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;

class DispatchLogStreamIdentifierToSteps implements BuildRequestTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        return $request->withSteps(array_map(function (BuildStepConfiguration $step) use ($request) {
            if (null !== $step->getLogStreamIdentifier()) {
                return $step;
            }

            if (null !== ($logging = $request->getLogging()) && null !== ($logStream = $logging->getLogStream())) {
                $identifier = $logStream->getParentLogIdentifier();
            } else {
                $identifier = '';
            }

            return $step->withLogStreamIdentifier($identifier);
        }, $request->getSteps()));
    }
}
