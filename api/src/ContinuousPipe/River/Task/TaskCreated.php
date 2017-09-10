<?php

namespace ContinuousPipe\River\Task;

use Ramsey\Uuid\UuidInterface;

class TaskCreated extends AbstractTaskEvent
{
    /**
     * @var array|null
     */
    private $configuration;

    public function __construct(UuidInterface $tideUuid, $taskIdentifier, \DateTimeInterface $dateTime = null, array $configuration = null)
    {
        parent::__construct($tideUuid, $taskIdentifier, $dateTime);

        $this->configuration = $configuration;
    }

    /**
     * @return array|null
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
