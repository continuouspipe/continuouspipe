<?php

namespace ContinuousPipe\River\Tests\CodeRepository\Status;

use ContinuousPipe\River\Tide\Status\CodeStatusUpdater;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Status\Status;
use Rhumsaa\Uuid\Uuid;

class FakeCodeStatusUpdater implements CodeStatusUpdater
{
    private $statuses = [];

    /**
     * {@inheritdoc}
     */
    public function success(Tide $tide)
    {
        $this->update($tide, new Status('success'));
    }

    /**
     * {@inheritdoc}
     */
    public function pending(Tide $tide)
    {
        $this->update($tide, new Status('pending'));
    }

    /**
     * {@inheritdoc}
     */
    public function failure(Tide $tide)
    {
        $this->update($tide, new Status('failure'));
    }

    /**
     * @param Uuid $uuid
     *
     * @return Status
     */
    public function getStatusForTideUuid(Uuid $uuid)
    {
        return $this->statuses[(string) $uuid];
    }

    /**
     * @param Uuid $uuid
     *
     * @return bool
     */
    public function hasStatusForTideUuid(Uuid $uuid)
    {
        return array_key_exists((string) $uuid, $this->statuses);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Tide $tide, Status $status)
    {
        $this->statuses[(string) $tide->getUuid()] = $status;
    }
}
