<?php

namespace AppBundle\Controller;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Run\RunTask;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param MessageBus     $eventBus
     * @param TideRepository $tideRepository
     */
    public function __construct(MessageBus $eventBus, TideRepository $tideRepository)
    {
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @Route("/runner/notification/tide/{tideUuid}", methods={"POST"}, name="runner_notification_post")
     * @ParamConverter("deployment", converter="fos_rest.request_body")
     * @View
     */
    public function postAction($tideUuid, Deployment $deployment)
    {
        $tideUuid = Uuid::fromString($tideUuid);

        $tide = $this->tideRepository->find($tideUuid);
        $tasks = $tide->getTasks()->ofType(RunTask::class);

        foreach ($tasks as $task) {
            $task->receiveDeploymentNotification($deployment);
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
