<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\Security\User\User;
use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class Tide
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FAILURE = 'failure';
    const STATUS_SUCCESS = 'success';

    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var Flow
     */
    private $flow;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @var User
     */
    private $user;

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

    private function __construct()
    {
    }

    /**
     * Create a new tide representation.
     *
     * @param Uuid          $uuid
     * @param Flow          $flow
     * @param CodeReference $codeReference
     * @param Log           $log
     * @param User          $user
     * @param array         $configuration
     * @param \DateTime     $creationDate
     *
     * @return Tide
     */
    public static function create(Uuid $uuid, Flow $flow, CodeReference $codeReference, Log $log, User $user, array $configuration, \DateTime $creationDate = null)
    {
        $tide = new self();
        $tide->uuid = $uuid;
        $tide->flow = $flow;
        $tide->codeReference = $codeReference;
        $tide->logId = $log->getId();
        $tide->creationDate = $creationDate ?: new \DateTime();
        $tide->user = $user;
        $tide->configuration = $configuration;

        return $tide;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return Flow
     */
    public function getFlow()
    {
        return $this->flow;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
