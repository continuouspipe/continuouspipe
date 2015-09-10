<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\TideContext;

class TaskContext extends TideContext
{
    const KEY_TASK_ID = 'taskId';

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
}
