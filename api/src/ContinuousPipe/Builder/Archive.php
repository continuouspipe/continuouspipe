<?php

namespace ContinuousPipe\Builder;

use Docker\Context\ContextInterface;

interface Archive extends ContextInterface
{
    /**
     * Delete the archive.
     */
    public function delete();
}
