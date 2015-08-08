<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Tide;

interface CodeStatusUpdater
{
    /**
     * Updates the status of the code related to that tide to successful.
     *
     * @param Tide $tide
     */
    public function success(Tide $tide);

    /**
     * Updates the status of the code related to that tide to pending.
     *
     * @param Tide $tide
     */
    public function pending(Tide $tide);

    /**
     * Updates the status of the code related to that tide to failure.
     *
     * @param Tide $tide
     */
    public function failure(Tide $tide);
}
