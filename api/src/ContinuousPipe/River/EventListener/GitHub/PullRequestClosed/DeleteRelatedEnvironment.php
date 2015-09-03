<?php

namespace ContinuousPipe\River\EventListener\GitHub\PullRequestClosed;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\ArrayContext;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Flow\Task;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;

class DeleteRelatedEnvironment
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @param Client $client
     * @param TideRepository $tideRepository
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     */
    public function __construct(Client $client, TideRepository $tideRepository, EnvironmentNamingStrategy $environmentNamingStrategy)
    {
        $this->client = $client;
        $this->tideRepository = $tideRepository;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
    }

    /**
     * @param PullRequestClosed $event
     */
    public function notify(PullRequestClosed $event)
    {
        $tides = $this->tideRepository->findByCodeReference($event->getCodeReference());

        foreach ($tides as $tide) {
            try {
                $target = $this->getTideTarget($tide);
            } catch (\LogicException $e) {
                continue;
            }

            $this->client->deleteEnvironment($target, $tide->getUser());
        }
    }

    /**
     * @param Tide $tide
     *
     * @return Client\DeploymentRequest\Target
     */
    private function getTideTarget(Tide $tide)
    {
        return new Client\DeploymentRequest\Target(
            $this->environmentNamingStrategy->getName(
                $tide->getFlow()->getUuid(),
                $tide->getCodeReference()
            ),
            $this->getProviderName($tide)
        );
    }

    /**
     * @param Tide $tide
     * @return string
     */
    private function getProviderName(Tide $tide)
    {
        $deployTask = $this->getDeployTask($tide);
        $context = new DeployContext(ArrayContext::fromRaw($deployTask->getContext()));

        return $context->getProviderName();
    }

    /**
     * @param Tide $tide
     * @throws \LogicException
     * @return Task
     */
    private function getDeployTask(Tide $tide)
    {
        $deployTasks = array_filter($tide->getFlow()->getTasks(), function(Task $task) {
            return $task->getName() == DeployTask::NAME;
        });

        if (0 == count($deployTasks)) {
            throw new \LogicException('Deploy task not found');
        }

        return current($deployTasks);
    }
}
