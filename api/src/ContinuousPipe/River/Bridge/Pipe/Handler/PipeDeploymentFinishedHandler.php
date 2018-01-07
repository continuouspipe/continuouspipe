<?php

namespace ContinuousPipe\River\Bridge\Pipe\Handler;

use ContinuousPipe\River\Bridge\Pipe\Command\PipeDeploymentFinishedCommand;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Run\RunTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class PipeDeploymentFinishedHandler
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;
    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TransactionManager $transactionManager, DeploymentRepository $deploymentRepository, LoggerInterface $logger)
    {
        $this->transactionManager = $transactionManager;
        $this->deploymentRepository = $deploymentRepository;
        $this->logger = $logger;
    }

    public function handle(PipeDeploymentFinishedCommand $command)
    {
        $deployment = $this->deploymentRepository->find($command->getDeploymentUuid());

        $attributes = $deployment->getRequest()->getAttributes();
        if (!isset($attributes['tide_uuid']) || !isset($attributes['task'])) {
            $this->logger->error('Was not able to know where this deployment come from', [
                'deployment_uuid' => $deployment->getUuid()->toString(),
                'attributes' => $attributes,
            ]);

            return;
        }

        $taskType = $attributes['task'];
        $this->transactionManager->apply(Uuid::fromString($attributes['tide_uuid']), function (Tide $tide) use ($deployment, $taskType) {
            /** @var DeployTask[] $tasks */
            if ($taskType == 'deploy') {
                $tasks = $tide->getTasks()->ofType(DeployTask::class);
            } elseif ($taskType == 'run') {
                /** @var RunTask[] $tasks */
                $tasks = $tide->getTasks()->ofType(RunTask::class);
            } else {
                throw new \RuntimeException(sprintf(
                    'Task type "%s" is invalid',
                    $taskType
                ));
            }

            if (empty($tasks)) {
                $this->logger->warning('Found no task to receive the notification', [
                    'tide_uuid' => $tide->getUuid()->toString(),
                    'task_type' => $taskType,
                    'deployment_uuid' => $deployment->getUuid()->toString(),
                ]);
            }

            foreach ($tasks as $task) {
                $task->receiveDeploymentNotification($deployment);
            }
        });
    }
}
