<?php

namespace ContinuousPipe\River\Tide\Status;

use ContinuousPipe\River\CodeRepository\CodeStatusException;
use ContinuousPipe\River\View\Tide;

interface CodeStatusUpdater
{
    /**
     * Updates the tide status.
     *
     * @param Tide   $tide
     * @param Status $status
     *
     * @throws CodeStatusException
     */
    public function update(Tide $tide, Status $status);
}
