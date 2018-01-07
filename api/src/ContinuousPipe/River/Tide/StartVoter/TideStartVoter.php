<?php

namespace ContinuousPipe\River\Tide\StartVoter;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;

interface TideStartVoter
{
    /**
     * Returns true if the voter consider that the tide should start.
     *
     * @param Tide                           $tide
     * @param Tide\Configuration\ArrayObject $context
     *
     * @throws TideConfigurationException
     *
     * @return bool
     */
    public function vote(Tide $tide, Tide\Configuration\ArrayObject $context);
}
