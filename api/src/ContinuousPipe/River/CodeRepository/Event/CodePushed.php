<?php

namespace ContinuousPipe\River\CodeRepository\Event;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryUser;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use Ramsey\Uuid\UuidInterface;

class CodePushed implements CodeRepositoryEvent
{
    private $flowUuid;
    private $codeReference;
    private $users;

    /**
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     * @param CodeRepositoryUser[] $users
     */
    public function __construct(UuidInterface $flowUuid, CodeReference $codeReference, array $users)
    {
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
        $this->users = $users;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    public function getCodeReference(): CodeReference
    {
        return $this->codeReference;
    }

    /**
     * @return CodeRepositoryUser[]
     */
    public function getUsers()
    {
        return $this->users;
    }
}
