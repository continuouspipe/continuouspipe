<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

class NullGitHubNotifier implements Notifier
{
    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return array_key_exists('github_commit_status', $configuration) && $configuration['github_commit_status'];
    }
}
