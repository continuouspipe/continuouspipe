<?php

namespace ContinuousPipe\River\Tide\Status;

use ContinuousPipe\River\CodeRepository\CodeStatusException;
use ContinuousPipe\River\Tide;

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

    /**
     * Updates the status of the code related to that tide to successful.
     *
     * @throws CodeStatusException
     *
     * @deprecated Should use the `update` method
     *
     * @param Tide $tide
     */
    public function success(Tide $tide);

    /**
     * Updates the status of the code related to that tide to pending.
     *
     * @throws CodeStatusException
     *
     * @deprecated Should use the `update` method
     *
     * @param Tide $tide
     */
    public function pending(Tide $tide);

    /**
     * Updates the status of the code related to that tide to failure.
     *
     * @throws CodeStatusException
     *
     * @deprecated Should use the `update` method
     *
     * @param Tide $tide
     */
    public function failure(Tide $tide);
}
