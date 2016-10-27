<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

class FilterNotificationBasedOnWhen implements Notifier
{
    /**
     * @var Notifier
     */
    private $decoratedNotifier;

    /**
     * @param Notifier $decoratedNotifier
     */
    public function __construct(Notifier $decoratedNotifier)
    {
        $this->decoratedNotifier = $decoratedNotifier;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        if (!$this->matchesWhen($status, $configuration)) {
            return;
        }

        $this->decoratedNotifier->notify($tide, $status, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return $this->decoratedNotifier->supports($tide, $status, $configuration);
    }

    /**
     * @param Status $status
     * @param array  $notification
     *
     * @return bool
     */
    private function matchesWhen(Status $status, array $notification)
    {
        if (!array_key_exists('when', $notification)) {
            return true;
        }

        return in_array($status->getState(), $notification['when']);
    }
}
