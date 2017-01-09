<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\Tide;

interface EnvironmentNamingStrategy
{
    /**
     * Get name of the environment.
     *
     * @param Tide        $tide
     * @param string|null $expression
     *
     * @throws UnresolvedEnvironmentNameException
     *
     * @return string
     */
    public function getName(Tide $tide, $expression = null);
}
