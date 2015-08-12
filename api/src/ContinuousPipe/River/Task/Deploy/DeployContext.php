<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\TideContext;

class DeployContext extends TideContext
{
    const PROVIDER_NAME_KEY = 'providerName';

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->get(self::PROVIDER_NAME_KEY);
    }
}
