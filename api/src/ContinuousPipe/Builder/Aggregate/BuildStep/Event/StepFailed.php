<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\Event;

class StepFailed extends StepEvent
{
    /**
     * @var \Throwable
     */
    private $reason;

    /**
     * @var string
     */
    private $logStreamIdentifier;

    public function __construct(string $buildIdentifier, int $stepPosition, \Throwable $reason, string $logStreamIdentifier)
    {
        parent::__construct($buildIdentifier, $stepPosition);

        $this->reason = $reason;
        $this->logStreamIdentifier = $logStreamIdentifier;
    }

    /**
     * @return \Throwable
     */
    public function getReason(): \Throwable
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getLogStreamIdentifier(): string
    {
        return $this->logStreamIdentifier;
    }
}
