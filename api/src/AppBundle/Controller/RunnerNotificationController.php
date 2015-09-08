<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Task\Run\Event\RunFailed;
use ContinuousPipe\River\Task\Run\Event\RunSuccessful;
use ContinuousPipe\Runner\Client\RunNotification;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route(service="app.controller.runner_notification")
 */
class RunnerNotificationController
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @Route("/runner/notification/tide/{tideUuid}", methods={"POST"}, name="runner_notification_post")
     * @ParamConverter("run", converter="fos_rest.request_body")
     */
    public function postAction($tideUuid, RunNotification $run)
    {
        $tideUuid = Uuid::fromString($tideUuid);

        if ($run->getStatus() == RunNotification::STATUS_SUCCESS) {
            $this->eventBus->handle(new RunSuccessful($tideUuid, $run));
        } elseif ($run->getStatus() == RunNotification::STATUS_FAILURE) {
            $this->eventBus->handle(new RunFailed($tideUuid, $run));
        } else {
            throw new \RuntimeException(sprintf(
                'Got a status of "%s" from runner',
                $run->getStatus()
            ));
        }
    }
}
