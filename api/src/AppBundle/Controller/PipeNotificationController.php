<?php

namespace AppBundle\Controller;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route(service="app.controller.pipe_notification")
 */
class PipeNotificationController
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
     * @Route("/pipe/notification/tide/{tideUuid}", methods={"POST"}, name="pipe_notification_post")
     * @ParamConverter("deployment", converter="fos_rest.request_body")
     */
    public function postAction($tideUuid, Deployment $deployment)
    {
        $tideUuid = Uuid::fromString($tideUuid);

        if ($deployment->isSuccessful()) {
            $this->eventBus->handle(new DeploymentSuccessful($tideUuid, $deployment));
        } elseif ($deployment->isFailed()) {
            $this->eventBus->handle(new DeploymentFailed($tideUuid, $deployment));
        }
    }
}
