<?php

namespace ContinuousPipe\River;

use ContinuousPipe\Security\Team\Team;

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

    /**
     * @return \Ramsey\Uuid\Uuid
     */
    public function getUuid()
    {
        return $this->getContext()->getFlowUuid();
    }

    /**
     * @return Team
     */
    public function getTeam() : Team
    {
        return $this->context->getTeam();
    }

    /**
     * @return array
     */
    public function getConfiguration() : array
    {
        return $this->context->getConfiguration() ?: [];
    }

    /**
     * @return CodeRepository
     */
    public function getCodeRepository() : CodeRepository
    {
        return $this->context->getCodeRepository();
    }
}
