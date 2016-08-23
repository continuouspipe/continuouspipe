<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\Model\Environment;
use Ramsey\Uuid\Uuid;

interface EnvironmentNamingStrategy
{
    /**
     * Get name of the environment.
     *
     * @param Uuid        $tideUuid
     * @param string|null $expression
     *
     * @throws UnresolvedEnvironmentNameException
     *
     * @return string
     */
    public function getName(Uuid $tideUuid, $expression = null);
}
