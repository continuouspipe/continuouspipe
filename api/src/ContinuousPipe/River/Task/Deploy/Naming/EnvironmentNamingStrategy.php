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

    /**
     * Returns true if the environment is part of the given flow.
     *
     * @deprecated That shouldn't be part of this interface. We should instead get the environments
     *             for a given flow.
     *
     * @param Uuid        $flowUuid
     * @param Environment $environment
     *
     * @return bool
     */
    public function isEnvironmentPartOfFlow(Uuid $flowUuid, Environment $environment);
}
