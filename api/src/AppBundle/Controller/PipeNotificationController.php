<?php

namespace AppBundle\Controller;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.pipe_notification")
 */
class PipeNotificationController
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
     * @Route("/pipe/notification/tide/{tideUuid}", methods={"POST"}, name="pipe_notification_post")
     * @ParamConverter("deployment", converter="fos_rest.request_body")
     * @View
     */
    public function postAction(string $tideUuid, Deployment $deployment)
    {
        $this->transactionManager->apply(Uuid::fromString($tideUuid), function (Tide $tide) use ($deployment) {
            /** @var DeployTask[] $tasks */
            $tasks = $tide->getTasks()->ofType(DeployTask::class);

            foreach ($tasks as $task) {
                $task->receiveDeploymentNotification($deployment);
            }
        });
    }
}
