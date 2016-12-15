<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use Ramsey\Uuid\Uuid;

class TaskFailed implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $taskIdentifier;

    /**
     * @var string
     */
    private $taskLogIdentifier;

    /**
     * @param Uuid   $tideUuid
     * @param string $taskIdentifier
     * @param string $taskLogIdentifier
     * @param string $message
     */
    public function __construct(Uuid $tideUuid, string $taskIdentifier, string $taskLogIdentifier, $message)
    {
        $this->tideUuid = $tideUuid;
        $this->message = $message;
        $this->taskIdentifier = $taskIdentifier;
        $this->taskLogIdentifier = $taskLogIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTaskIdentifier(): string
    {
        return $this->taskIdentifier;
    }

    /**
     * @return string
     */
    public function getTaskLogIdentifier(): string
    {
        return $this->taskLogIdentifier;
    }
}
