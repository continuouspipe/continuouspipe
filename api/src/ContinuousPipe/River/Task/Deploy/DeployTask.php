<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\TideContext;

class DeployTask extends EventDrivenTask
{
    /**
     * @param TideContext $context
     */
    public function start(TideContext $context)
    {
        var_dump('start deploy', $context);
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return false;
    }
}
