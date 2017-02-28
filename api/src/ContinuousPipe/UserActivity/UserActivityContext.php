<?php

namespace ContinuousPipe\UserActivity;

use Ramsey\Uuid\Uuid;

class UserActivityContext
{
    /**
     * @var string
     */
    private $teamSlug;

    /**
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @var Uuid
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
     * @return Uuid|null
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }

    public function setFlowUuid(Uuid $flowUuid)
    {
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return Uuid|null
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    public function setTideUuid(Uuid $tideUuid)
    {
        $this->tideUuid = $tideUuid;
    }
}
