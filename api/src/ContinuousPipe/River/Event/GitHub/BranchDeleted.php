<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use Ramsey\Uuid\UuidInterface;

class BranchDeleted implements CodeRepositoryEvent
{
    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     */
    public function __construct(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }
}
