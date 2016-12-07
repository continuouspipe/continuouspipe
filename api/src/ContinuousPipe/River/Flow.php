<?php

namespace ContinuousPipe\River;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class Flow
{
    /**
     * @var FlowContext
     */
    private $context;

    /**
     * @param FlowContext $context
     */
    public function __construct(FlowContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return FlowContext
     */
    public function getContext()
    {
        return $this->context;
    }

    public function getUuid() : UuidInterface
    {
        return $this->context->getFlowUuid();
    }

    public function getTeam() : Team
    {
        return $this->context->getTeam();
    }

    public function getConfiguration() : array
    {
        return $this->context->getConfiguration() ?: [];
    }

    public function getCodeRepository() : CodeRepository
    {
        return $this->context->getCodeRepository();
    }

    public function getUser() : User
    {
        return $this->context->getUser();
    }
}
