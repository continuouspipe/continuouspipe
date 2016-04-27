<?php

namespace ContinuousPipe\River\Infrastructure\InMemory;

use ContinuousPipe\River\View\TimeResolver;

class NativeTimeResolver implements TimeResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve()
    {
        return \DateTime::createFromFormat('U.u', microtime(true));
    }
}
