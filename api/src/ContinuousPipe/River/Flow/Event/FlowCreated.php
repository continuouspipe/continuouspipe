<?php

namespace ContinuousPipe\River\Flow\Event;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class FlowCreated implements FlowEvent
{
    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var Team
     */
    private $team;

    /**
     * @var User
     */
    private $user;

    /**
     * @var CodeRepository
     */
    private $codeRepository;

    /**
     * @param UuidInterface  $flowUuid
     * @param Team           $team
     * @param User           $user
     * @param CodeRepository $codeRepository
     */
    public function __construct(UuidInterface $flowUuid, Team $team, User $user, CodeRepository $codeRepository)
    {
        $this->flowUuid = $flowUuid;
        $this->team = $team;
        $this->user = $user;
        $this->codeRepository = $codeRepository;
    }

    public function getFlowUuid() : UuidInterface
    {
        return $this->flowUuid;
    }

    public function getTeam() : Team
    {
        return $this->team;
    }

    public function getUser() : User
    {
        return $this->user;
    }

    public function getCodeRepository() : CodeRepository
    {
        return $this->codeRepository;
    }
}
