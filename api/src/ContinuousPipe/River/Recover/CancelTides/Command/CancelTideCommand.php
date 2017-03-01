<?php

namespace ContinuousPipe\River\Recover\CancelTides\Command;

use ContinuousPipe\River\Command\TideCommand;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class CancelTideCommand implements TideCommand
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
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

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }
}
