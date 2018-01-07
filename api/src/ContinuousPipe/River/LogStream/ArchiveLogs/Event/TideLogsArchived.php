<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\Event;

use ContinuousPipe\River\Event\TideEvent;
use Ramsey\Uuid\Uuid;

class TideLogsArchived implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @param Uuid $tideUuid
     */
    public function __construct(Uuid $tideUuid)
    {
        $this->tideUuid = $tideUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }
}
