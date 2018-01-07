<?php

namespace ContinuousPipe\River\Notifications\Asynchronous\Command;

use ContinuousPipe\Message\Message;
use ContinuousPipe\River\Message\OperationalMessage;
use ContinuousPipe\River\Tide\Status\Status;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class NotifyCommand implements OperationalMessage
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @JMS\Type("ContinuousPipe\River\Tide\Status\Status")
     *
     * @var Status
     */
    private $status;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $configuration;

    public function __construct(UuidInterface $tideUuid, Status $status, array $configuration)
    {
        $this->tideUuid = $tideUuid;
        $this->status = $status;
        $this->configuration = $configuration;
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
