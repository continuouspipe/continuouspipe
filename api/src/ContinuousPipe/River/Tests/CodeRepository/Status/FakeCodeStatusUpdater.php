<?php

namespace ContinuousPipe\River\Tests\CodeRepository\Status;

use ContinuousPipe\River\CodeRepository\CodeStatusUpdater;
use ContinuousPipe\River\Tide;
use Rhumsaa\Uuid\Uuid;

class FakeCodeStatusUpdater implements CodeStatusUpdater
{
    private $statuses = [];

    /**
     * {@inheritdoc}
     */
    public function success(Tide $tide)
    {
        $this->statuses[(string) $tide->getUuid()] = 'success';
    }

    /**
     * {@inheritdoc}
     */
    public function pending(Tide $tide)
    {
        $this->statuses[(string) $tide->getUuid()] = 'pending';
    }

    /**
     * {@inheritdoc}
     */
    public function failure(Tide $tide)
    {
        $this->statuses[(string) $tide->getUuid()] = 'failure';
    }

    /**
     * @param Uuid $uuid
     *
     * @return string
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
}
