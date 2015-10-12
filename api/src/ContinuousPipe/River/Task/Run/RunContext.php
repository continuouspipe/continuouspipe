<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\Task\TaskContext;

class RunContext extends TaskContext
{
    const KEY_SERVICE_NAME = 'service';
    const KEY_IMAGE_NAME = 'image';
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
     * @return string
     */
    public function getImageName()
    {
        return $this->get(self::KEY_IMAGE_NAME);
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        $commands = $this->get(self::KEY_COMMANDS);
        if (!is_array($commands)) {
            $commands = explode("\n", $commands);
        }

        return $commands;
    }
}
