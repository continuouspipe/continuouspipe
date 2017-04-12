<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;

class EngineIntoStepsTransformer implements BuildRequestTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        $engine = $request->getEngine();
        
        if(!isset($engine)) {
            return $request;
        }
        
        return $request->withSteps(
            array_map(function(BuildStepConfiguration $step) use ($engine) {
                return $step->withEngine($engine);
            }, $request->getSteps())
        );
    }
}
