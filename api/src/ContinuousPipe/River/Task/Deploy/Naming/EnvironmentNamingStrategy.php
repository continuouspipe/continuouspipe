<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\CodeReference;
use Rhumsaa\Uuid\Uuid;

class EnvironmentNamingStrategy
{
    /**
     * Get name of the deployed environment.
     *
     * @param Uuid          $flowUuid
     * @param CodeReference $codeReference
     *
     * @return string
     */
    public function getName(Uuid $flowUuid, CodeReference $codeReference)
    {
        return sprintf('%s-%s', (string) $flowUuid, $codeReference->getBranch());
    }

    /**
     * Returns true if the environment is part of the given flow.
     *
     * @param Uuid        $flowUuid
     * @param Environment $environment
     *
     * @return bool
     */
    public function isEnvironmentPartOfFlow(Uuid $flowUuid, Environment $environment)
    {
        return strpos($environment->getName(), (string) $flowUuid) === 0;
    }
}
