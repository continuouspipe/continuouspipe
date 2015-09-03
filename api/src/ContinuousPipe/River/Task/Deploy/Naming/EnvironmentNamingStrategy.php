<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\River\CodeReference;
use Rhumsaa\Uuid\Uuid;

class EnvironmentNamingStrategy
{
    /**
     * Get name of the deployment environment.
     *
     * @param Uuid $flowUuid
     * @param CodeReference $codeReference
     *
     * @return string
     */
    public function getName(Uuid $flowUuid, CodeReference $codeReference)
    {
        return sprintf('%s-%s', (string) $flowUuid, $codeReference->getBranch());
    }
}
