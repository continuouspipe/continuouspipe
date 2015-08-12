<?php

namespace AppBundle\Controller;

use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\BuildSuccessful;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route(service="app.controller.builder_notification")
 */
class BuilderNotificationController
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
     * @Route("/builder/notification/tide/{tideUuid}", methods={"POST"}, name="builder_notification_post")
     * @ParamConverter("build", converter="fos_rest.request_body")
     */
    public function postAction($tideUuid, BuilderBuild $build)
    {
        $tideUuid = Uuid::fromString($tideUuid);

        if ($build->isSuccessful()) {
            $this->eventBus->handle(new BuildSuccessful($tideUuid, $build));
        } elseif ($build->isErrored()) {
            $this->eventBus->handle(new BuildFailed($tideUuid, $build));
        }
    }
}
