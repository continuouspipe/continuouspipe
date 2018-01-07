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
        $microTime = sprintf('%01.4f', microtime(true));

        return \DateTime::createFromFormat('U.u', $microTime);
    }
}
