<?php

namespace ContinuousPipe\UserActivity;

use Ramsey\Uuid\UuidInterface;

class UserActivityContext
{
    /**
     * @var string
     */
    private $teamSlug;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @return string|null
     */
    public function getTeamSlug()
    {
        return $this->teamSlug;
    }

    public function setTeamSlug(string $teamSlug)
    {
        $this->teamSlug = $teamSlug;
    }

    /**
     * @return UuidInterface|null
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }

    public function setFlowUuid(UuidInterface $flowUuid)
    {
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return UuidInterface|null
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    public function setTideUuid(UuidInterface $tideUuid)
    {
        $this->tideUuid = $tideUuid;
    }
}
