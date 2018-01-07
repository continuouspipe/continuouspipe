<?php

namespace ContinuousPipe\River\View\Storage;

use ContinuousPipe\River\View\Tide;

interface TideViewStorage
{
    /**
     * Save the tide representation.
     *
     * @param Tide $tide
     */
    public function save(Tide $tide);
}
