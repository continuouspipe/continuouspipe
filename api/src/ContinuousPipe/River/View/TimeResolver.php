<?php

namespace ContinuousPipe\River\View;

interface TimeResolver
{
    /**
     * @return \DateTime
     */
    public function resolve();
}
