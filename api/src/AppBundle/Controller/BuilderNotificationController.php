<?php

namespace AppBundle\Controller;

use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\River\Task\Build\Command\ReceiveBuildNotification;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.builder_notification")
 */
class BuilderNotificationController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @Route("/builder/notification/tide/{tideUuid}", methods={"POST"}, name="builder_notification_post")
     * @ParamConverter("build", converter="fos_rest.request_body")
     * @View
     */
    public function postAction($tideUuid, BuilderBuild $build)
    {
        $this->commandBus->handle(new ReceiveBuildNotification(
            Uuid::fromString($tideUuid),
            $build
        ));
    }
}
