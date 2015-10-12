<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\TideContext;
use LogStream\Log;

class TaskContext extends TideContext
{
    const KEY_TASK_ID = 'taskId';
    const TASK_LOG = 'taskLog';

    /**
     * @param Context $parent
     * @param int     $taskId
     *
     * @return TaskContext
     */
    public static function createTaskContext(Context $parent, $taskId)
    {
        $context = new self($parent);
        $context->set(self::KEY_TASK_ID, $taskId);

        return $context;
    }

    /**
     * @return string
     */
    public function getTaskId()
    {
        return $this->get(self::KEY_TASK_ID);
    }

    /**
     * @return Log
     */
    public function getTaskLog()
    {
        if (!$this->has(self::TASK_LOG)) {
            return;
        }

        return $this->get(self::TASK_LOG);
    }

    /**
     * @param Log $log
     */
    public function setTaskLog(Log $log)
    {
        return $this->set(self::TASK_LOG, $log);
    }
}
