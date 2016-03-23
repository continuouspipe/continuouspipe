<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\River\Flow;

class BranchDeleted implements CodeRepositoryEvent
{
    /**
     * @var Flow
     */
    private $flow;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @param Flow          $flow
     * @param CodeReference $codeReference
     */
    public function __construct(Flow $flow, CodeReference $codeReference)
    {
        $this->flow = $flow;
        $this->codeReference = $codeReference;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }
}
