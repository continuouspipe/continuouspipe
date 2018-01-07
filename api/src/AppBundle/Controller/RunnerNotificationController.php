<?php

namespace AppBundle\Controller;

use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\River\Task\Run\RunTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.runner_notification")
 */
class RunnerNotificationController
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param TransactionManager $transactionManager
     */
    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    /**
     * @Route("/runner/notification/tide/{tideUuid}", methods={"POST"}, name="runner_notification_post")
     * @ParamConverter("deployment", converter="fos_rest.request_body")
     * @View
     */
    public function postAction(string $tideUuid, Deployment $deployment)
    {
        $this->transactionManager->apply(Uuid::fromString($tideUuid), function (Tide $tide) use ($deployment) {
            /** @var RunTask[] $tasks */
            $tasks = $tide->getTasks()->ofType(RunTask::class);

            foreach ($tasks as $task) {
                $task->receiveDeploymentNotification($deployment);
            }
        });
    }
}
