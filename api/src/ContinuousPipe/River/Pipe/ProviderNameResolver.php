<?php

namespace ContinuousPipe\River\Pipe;

use ContinuousPipe\River\ArrayContext;
use ContinuousPipe\River\Flow\Task;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeployTask;

class ProviderNameResolver
{
    /**
     * @param Flow $flow
     *
     * @return string
     */
    public function getProviderName(Flow $flow)
    {
        $deployTask = $this->getDeployTask($flow);
        $context = new DeployContext(ArrayContext::fromRaw($deployTask->getContext()));

        return $context->getProviderName();
    }

    /**
     * @param Flow $flow
     *
     * @throws \LogicException
     *
     * @return Task
     */
    private function getDeployTask(Flow $flow)
    {
        $deployTasks = array_filter($flow->getTasks(), function (Task $task) {
            return $task->getName() == DeployTask::NAME;
        });

        if (0 == count($deployTasks)) {
            throw new \LogicException('Deploy task not found');
        }

        return current($deployTasks);
    }
}
