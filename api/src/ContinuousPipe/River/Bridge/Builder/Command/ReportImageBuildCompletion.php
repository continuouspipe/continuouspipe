<?php


namespace ContinuousPipe\River\Bridge\Builder\Command;

use Ramsey\Uuid\UuidInterface;

class ReportImageBuildCompletion
{
    /**
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @var string
     */
    private $buildIdentifier;

    public function __construct(UuidInterface $tideUuid, string $buildIdentifier)
    {
        $this->tideUuid = $tideUuid;
        $this->buildIdentifier = $buildIdentifier;
    }

    /**
     * @return UuidInterface
     */
    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getBuildIdentifier(): string
    {
        return $this->buildIdentifier;
    }
}
