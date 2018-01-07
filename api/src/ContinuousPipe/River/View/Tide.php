<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use LogStream\Log;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class Tide
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FAILURE = 'failure';
    const STATUS_SUCCESS = 'success';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @JMS\Groups({"Default"})
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var User
     */
    private $user;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var Team
     */
    private $team;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $status;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $logId;

    /**
     * @JMS\Groups({"Configuration"})
     *
     * @var array
     */
    private $configuration;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var \DateTime
     */
    private $startDate;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var \DateTime
     */
    private $finishDate;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var TideTaskView[]
     */
    private $tasks = [];

    /**
     * @JMS\Groups({"Default"})
     *
     * @var UuidInterface|null
     */
    private $generationUuid;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var FlatPipeline|null
     */
    private $pipeline;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default"})
     *
     * @var string|null
     */
    private $failureReason;

    private function __construct()
    {
    }

    /**
     * Create a new tide representation.
     *
     * @param UuidInterface      $uuid
     * @param UuidInterface      $flowUuid
     * @param CodeReference      $codeReference
     * @param Log                $log
     * @param Team               $team
     * @param User               $user
     * @param array              $configuration
     * @param \DateTime          $creationDate
     * @param UuidInterface|null $generationUuid
     * @param FlatPipeline|null  $pipeline
     * @param string|null        $failureReason
     *
     * @return Tide
     */
    public static function create(
        UuidInterface $uuid,
        UuidInterface $flowUuid,
        CodeReference $codeReference,
        Log $log,
        Team $team,
        User $user,
        array $configuration,
        \DateTime $creationDate,
        UuidInterface $generationUuid = null,
        FlatPipeline $pipeline = null,
        string $failureReason = null
    ) {
        $tide = new self();
        $tide->uuid = $uuid;
        $tide->flowUuid = $flowUuid;
        $tide->codeReference = $codeReference;
        $tide->logId = $log->getId();
        $tide->creationDate = $creationDate;
        $tide->team = $team;
        $tide->user = $user;
        $tide->configuration = $configuration;
        $tide->generationUuid = $generationUuid;
        $tide->pipeline = $pipeline;
        $tide->failureReason = $failureReason;

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

    /**
     * @return null|UuidInterface
     */
    public function getGenerationUuid()
    {
        return $this->generationUuid;
    }

    /**
     * @return null|FlatPipeline
     */
    public function getPipeline()
    {
        return $this->pipeline;
    }

    /**
     * @return null|string
     */
    public function getFailureReason()
    {
        return $this->failureReason;
    }
}
