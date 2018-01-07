<?php

namespace ContinuousPipe\River\Pipe;

use ContinuousPipe\River\Flow\Task;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\View\Tide;

class ClusterIdentifierResolver
{
    /**
     * @param Tide $tide
     *
     * @throws ClusterIdentifierNotFound
     *
     * @return string
     */
    public function getClusterIdentifier(Tide $tide)
    {
        $tideConfiguration = $tide->getConfiguration();
        if (!array_key_exists('tasks', $tideConfiguration)) {
            throw new ClusterIdentifierNotFound('No task configuration found');
        }

        $tasks = $tideConfiguration['tasks'];
        $deployTasks = array_filter($tasks, function (array $task) {
            return array_key_exists(DeployTask::NAME, $task);
        });

        if (count($deployTasks) == 0) {
            throw new ClusterIdentifierNotFound('No deploy task found in tide');
        }

        $deployTask = current($deployTasks)[DeployTask::NAME];
        if (!array_key_exists('cluster', $deployTask)) {
            throw new ClusterIdentifierNotFound('No cluster identifier found in deploy task configuration');
        }

        return $deployTask['cluster'];
    }
}
