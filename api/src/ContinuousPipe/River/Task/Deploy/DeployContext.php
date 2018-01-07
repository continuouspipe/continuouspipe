<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\Task\TaskContext;

class DeployContext extends TaskContext
{
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
}
