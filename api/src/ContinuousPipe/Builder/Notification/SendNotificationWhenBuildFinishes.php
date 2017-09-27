<?php

namespace ContinuousPipe\Builder\Notification;

use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\View\BuildViewRepository;
use ContinuousPipe\Builder\Notifier;

class SendNotificationWhenBuildFinishes
{
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var \ContinuousPipe\Builder\View\BuildViewRepository
     */
    private $buildRepository;

    /**
     * @param Notifier        $notifier
     * @param \ContinuousPipe\Builder\View\BuildViewRepository $buildRepository
     */
    public function __construct(Notifier $notifier, BuildViewRepository $buildRepository)
    {
        $this->notifier = $notifier;
        $this->buildRepository = $buildRepository;
    }

    /**
     * @param BuildEvent $event
     */
    public function notify(BuildEvent $event)
    {
        $build = $this->buildRepository->find($event->getBuildIdentifier());

        if (null !== ($notification = $build->getRequest()->getNotification())) {
            $this->notifier->notify($notification, $build);
        }
    }
}
