<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;

class TaskQueued extends TaskLogCreated implements TideEvent, TaskEvent
{
}
