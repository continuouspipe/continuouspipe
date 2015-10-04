<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\Task\TaskContext;
use LogStream\Log;

class DeployContext extends TaskContext
{
    const PROVIDER_NAME_KEY = 'providerName';
    const DEPLOY_LOG_KEY = 'deployLog';

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

    /**
     * @return Log
     */
    public function getTaskLog()
    {
        return $this->get(self::DEPLOY_LOG_KEY);
    }

    /**
     * @param Log $log
     */
    public function setTaskLog(Log $log)
    {
        $this->set(self::DEPLOY_LOG_KEY, $log);
    }
}
