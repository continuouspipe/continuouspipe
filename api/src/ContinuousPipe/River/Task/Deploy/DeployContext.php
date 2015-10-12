<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\Task\TaskContext;

class DeployContext extends TaskContext
{
    const PROVIDER_NAME_KEY = 'providerName';

    /**
     * @param Context $parent
     *
     * @return DeployContext
     */
    public static function createDeployContext(Context $parent)
    {
        $context = new self($parent);

        return $context;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->get(self::PROVIDER_NAME_KEY);
    }
}
