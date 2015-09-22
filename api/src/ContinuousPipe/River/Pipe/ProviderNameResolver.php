<?php

namespace ContinuousPipe\River\Pipe;

use ContinuousPipe\River\Flow\Task;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\View\Tide;

class ProviderNameResolver
{
    /**
     * @param Tide $tide
     *
     * @throws ProviderNameNotFound
     *
     * @return string
     */
    public function getProviderName(Tide $tide)
    {
        $tideConfiguration = $tide->getConfiguration();
        if (!array_key_exists('tasks', $tideConfiguration)) {
            throw new ProviderNameNotFound('No task configuration found');
        }

        $tasks = $tideConfiguration['tasks'];
        $deployTasks = array_filter($tasks, function (array $task) {
            return array_key_exists(DeployTask::NAME, $task);
        });

        if (count($deployTasks) == 0) {
            throw new ProviderNameNotFound('No deploy task found in tide');
        }

        $deployTask = current($deployTasks)[DeployTask::NAME];
        if (!array_key_exists('providerName', $deployTask)) {
            throw new ProviderNameNotFound('No provider name found in deploy task configuration');
        }

        return $deployTask['providerName'];
    }
}
