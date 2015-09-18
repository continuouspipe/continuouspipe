<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\Task\TaskContext;
use LogStream\Log;

class RunContext extends TaskContext
{
    const KEY_SERVICE_NAME = 'service';
    const KEY_COMMANDS = 'commands';
    const KEY_RUNNER_LOG = 'runnerLog';

    /**
     * @param Context $parent
     *
     * @return RunContext
     */
    public static function createRunContext(Context $parent)
    {
        return new self($parent);
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->get(self::KEY_SERVICE_NAME);
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return explode("\n", $this->get(self::KEY_COMMANDS));
    }

    /**
     * @return Log
     */
    public function getRunnerLog()
    {
        return $this->get(self::KEY_RUNNER_LOG);
    }

    /**
     * @param Log $log
     */
    public function setRunnerLog(Log $log)
    {
        $this->set(self::KEY_RUNNER_LOG, $log);
    }
}
