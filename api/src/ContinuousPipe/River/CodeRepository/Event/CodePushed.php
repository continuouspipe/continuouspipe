<?php

namespace ContinuousPipe\River\CodeRepository\Event;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use Ramsey\Uuid\UuidInterface;

class CodePushed implements CodeRepositoryEvent
{
    private $flowUuid;
    private $codeReference;

    public function __construct(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    public function getCodeReference(): CodeReference
    {
        return $this->codeReference;
    }
}
