<?php

namespace AppBundle\Controller;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.builder_notification")
 *
 * @deprecated This is now a PHP method call, no need for such HTTP adapter.
 */
class BuilderNotificationController
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
     * @Route("/builder/notification/tide/{tideUuid}", methods={"POST"}, name="builder_notification_post")
     * @ParamConverter("build", converter="fos_rest.request_body")
     * @View
     */
    public function postAction($tideUuid, Build $build)
    {
        $this->transactionManager->apply(Uuid::fromString($tideUuid), function (Tide $tide) use ($build) {
            /** @var BuildTask[] $tasks */
            $tasks = $tide->getTasks()->ofType(BuildTask::class);

            foreach ($tasks as $task) {
                $task->receiveBuildNotification($build);
            }
        });
    }
}
