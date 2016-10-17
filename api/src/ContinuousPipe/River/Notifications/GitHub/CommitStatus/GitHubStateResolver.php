<?php

namespace ContinuousPipe\River\Notifications\GitHub\CommitStatus;

use ContinuousPipe\River\Tide\Status\Status;

class GitHubStateResolver
{
    /**
     * @param Status $status
     *
     * @return string
     */
    public function fromStatus(Status $status)
    {
        $state = $status->getState();

        if ($state == 'running') {
            $state = 'pending';
        }

        if (!in_array($state, ['pending', 'success', 'error', 'failure'])) {
            $state = 'error';
        }

        return $state;
    }
}
