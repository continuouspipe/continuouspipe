<?php


namespace ContinuousPipe\River\CodeRepository\GitHub\CommitStatuses;

use ContinuousPipe\River\Notifications\NotificationException;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

class TruncateStatusDescription implements Notifier
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
        return $this->decoratedNotifier->notify(
            $tide,
            $status->withDescription(
                $this->truncateDescriptionIfNeeded($status->getDescription())
            ),
            $configuration
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return $this->decoratedNotifier->supports($tide, $status, $configuration);
    }

    private function truncateDescriptionIfNeeded(string $description) : string
    {
        if (strlen($description) > 140) {
            $description = substr($description, 0, 137).'...';
        }

        return $description;
    }
}
