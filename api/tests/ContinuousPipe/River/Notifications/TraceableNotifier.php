<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

class TraceableNotifier implements Notifier
{
    private $notifications = [];

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
        $this->decoratedNotifier->notify($tide, $status, $configuration);

        $this->notifications[] = $status;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return $this->decoratedNotifier->supports($tide, $status, $configuration);
    }

    /**
     * @return array
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }
}
