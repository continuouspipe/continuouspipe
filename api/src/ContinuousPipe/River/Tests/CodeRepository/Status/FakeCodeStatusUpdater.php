<?php

namespace ContinuousPipe\River\Tests\CodeRepository\Status;

use ContinuousPipe\River\Tide\Status\CodeStatusUpdater;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\Tide\Status\Status;
use Ramsey\Uuid\Uuid;

class FakeCodeStatusUpdater implements CodeStatusUpdater
{
    private $statuses = [];

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
