<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;

class AddDefaultEngine implements BuildRequestTransformer
{
    /**
     * @var string
     */
    private $defaultEngine;

    public function __construct(string $defaultEngine)
    {
        $this->defaultEngine = $defaultEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        if (null === $request->getEngine()) {
            $request = $request->withEngine(new Engine($this->defaultEngine));
        }

        return $request;
    }
}
