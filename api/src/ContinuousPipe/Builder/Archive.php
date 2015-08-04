<?php

namespace ContinuousPipe\Builder;

use Docker\Context\ContextInterface;

interface Archive extends ContextInterface
{
    /**
     * Get contents of the archive.
     *
     * @return string
     */
    public function getContents();
}
