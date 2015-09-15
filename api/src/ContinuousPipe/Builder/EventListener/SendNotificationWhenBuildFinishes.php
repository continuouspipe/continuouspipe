<?php

namespace ContinuousPipe\Builder\EventListener;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Event\BuildEvent;
use ContinuousPipe\Builder\Event\BuildFailed;
use ContinuousPipe\Builder\Event\BuildSuccessful;
use ContinuousPipe\Builder\Notifier;

class SendNotificationWhenBuildFinishes
{
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @param Notifier        $notifier
     * @param BuildRepository $buildRepository
     */
    public function __construct(Notifier $notifier, BuildRepository $buildRepository)
    {
        $this->notifier = $notifier;
        $this->buildRepository = $buildRepository;
    }

    /**
     * @param BuildEvent $event
     */
    public function notify(BuildEvent $event)
    {
        $build = $event->getBuild();

        if ($event instanceof BuildFailed) {
            $build->updateStatus(Build::STATUS_ERROR);
        } elseif ($event instanceof BuildSuccessful) {
            $build->updateStatus(Build::STATUS_SUCCESS);
        }

        $build = $this->buildRepository->save($build);

        $this->sendNotificationForBuild($build);
    }

    /**
     * @param Build $build
     */
    private function sendNotificationForBuild(Build $build)
    {
        $notification = $build->getRequest()->getNotification();
        if (null !== $notification) {
            $this->notifier->notify($notification, $build);
        }
    }
}
