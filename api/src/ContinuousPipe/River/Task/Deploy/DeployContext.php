<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\TideContext;
use LogStream\Log;

class DeployContext extends TideContext
{
    const PROVIDER_NAME_KEY = 'providerName';
    const DEPLOY_LOG_KEY = 'deployLog';

    /**
     * @param Context $parent
     * @param Log $log
     * @return DeployContext
     */
    public static function createDeployContext(Context $parent, Log $log)
    {
        $context = new self($parent);
        $context->set(self::DEPLOY_LOG_KEY, $log);

        return $context;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->get(self::PROVIDER_NAME_KEY);
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->get(self::DEPLOY_LOG_KEY);
    }
}
