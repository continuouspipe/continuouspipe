<?php

namespace ContinuousPipe\River\Flow\Event;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class FlowCreated implements FlowEvent
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Type("ContinuousPipe\Security\Team\Team")
     *
     * @var Team
     */
    private $team;

    /**
     * @JMS\Type("ContinuousPipe\Security\User\User")
     *
     * @var User
     */
    private $user;

    /**
     * @JMS\Type("ContinuousPipe\River\AbstractCodeRepository")
     *
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
