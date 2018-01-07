<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestException;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;

class ChainBuildRequestTransformer implements BuildRequestTransformer
{
    /**
     * @var BuildRequestTransformer[]
     */
    private $transformers;

    /**
     * @param BuildRequestTransformer[] $transformers
     */
    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        foreach ($this->transformers as $transformer) {
            $request = $transformer->transform($request);
        }

        return $request;
    }
}
