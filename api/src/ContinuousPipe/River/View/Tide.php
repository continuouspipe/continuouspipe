<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use LogStream\Log;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Tide
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FAILURE = 'failure';
    const STATUS_SUCCESS = 'success';

    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Team
     */
    private $team;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $logId;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $finishDate;

    /**
     * @var TideTaskView[]
     */
    private $tasks = [];

    private function __construct()
    {
    }

    /**
     * Create a new tide representation.
     *
     * @param UuidInterface $uuid
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     * @param Log           $log
     * @param Team          $team
     * @param User          $user
     * @param array         $configuration
     * @param \DateTime     $creationDate
     *
     * @return Tide
     */
    public static function create(UuidInterface $uuid, UuidInterface $flowUuid, CodeReference $codeReference, Log $log, Team $team, User $user, array $configuration, \DateTime $creationDate)
    {
        $tide = new self();
        $tide->uuid = $uuid;
        $tide->flowUuid = $flowUuid;
        $tide->codeReference = $codeReference;
        $tide->logId = $log->getId();
        $tide->creationDate = $creationDate;
        $tide->team = $team;
        $tide->user = $user;
        $tide->configuration = $configuration;

        return $tide;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getFinishDate()
    {
        return $this->finishDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param \DateTime $finishDate
     */
    public function setFinishDate($finishDate)
    {
        $this->finishDate = $finishDate;
    }

    /**
     * @return TideTaskView[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @param TideTaskView[] $tasks
     */
    public function setTasks(array $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
