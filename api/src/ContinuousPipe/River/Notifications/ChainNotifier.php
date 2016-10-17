<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

class ChainNotifier implements Notifier
{
    /**
     * @var array|Notifier[]
     */
    private $notifiers;

    /**
     * @param Notifier[] $notifiers
     */
    public function __construct(array $notifiers)
    {
        $this->notifiers = $notifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        foreach ($this->notifiers as $notifier) {
            if ($notifier->supports($tide, $status, $configuration)) {
                $notifier->notify($tide, $status, $configuration);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        foreach ($this->notifiers as $notifier) {
            if ($notifier->supports($tide, $status, $configuration)) {
                return true;
            }
        }

        return false;
    }
}
