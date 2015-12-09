<?php

namespace ContinuousPipe\River\Tide\StartVoter;

use ContinuousPipe\River\Tide;

class UnanimousVoter implements TideStartVoter
{
    /**
     * @var TideStartVoter[]
     */
    private $voters;

    /**
     * @param TideStartVoter[] $voters
     */
    public function __construct(array $voters)
    {
        $this->voters = $voters;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(Tide $tide, Tide\Configuration\ArrayObject $context)
    {
        foreach ($this->voters as $voter) {
            if (false === $voter->vote($tide, $context)) {
                return false;
            }
        }

        return true;
    }
}
