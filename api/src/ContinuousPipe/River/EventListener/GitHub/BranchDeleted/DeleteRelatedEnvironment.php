<?php

namespace ContinuousPipe\River\EventListener\GitHub\BranchDeleted;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Event\GitHub\BranchDeleted;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Psr\Log\LoggerInterface;

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
     * @var ClusterIdentifierResolver
     */
    private $clusterIdentifierResolver;

    /**
     * @var LoggerInterface
     */
    private $systemLogger;
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param Client                    $client
     * @param TideRepository            $tideRepository
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param ClusterIdentifierResolver $clusterIdentifierResolver
     * @param LoggerInterface           $systemLogger
     * @param EventStore                $eventStore
     */
    public function __construct(Client $client, TideRepository $tideRepository, EnvironmentNamingStrategy $environmentNamingStrategy, ClusterIdentifierResolver $clusterIdentifierResolver, LoggerInterface $systemLogger, EventStore $eventStore)
    {
        $this->client = $client;
        $this->tideRepository = $tideRepository;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->clusterIdentifierResolver = $clusterIdentifierResolver;
        $this->systemLogger = $systemLogger;
        $this->eventStore = $eventStore;
    }

    /**
     * @param BranchDeleted $event
     */
    public function notify(BranchDeleted $event)
    {
        $tides = $this->tideRepository->findByCodeReference($event->getCodeReference());

        foreach ($tides as $tide) {
            $targets = $this->getTideTargets($tide);

            foreach ($targets as $target) {
                $this->client->deleteEnvironment($target, $tide->getTeam(), $tide->getUser());
            }
        }
    }

    /**
     * @param Tide $tide
     *
     * @return Client\DeploymentRequest\Target[]
     */
    private function getTideTargets(Tide $tide)
    {
        return array_map(function (DeploymentStarted $deploymentStarted) {
            return $deploymentStarted->getDeployment()->getRequest()->getTarget();
        }, $this->getStartedDeployments($tide));
    }

    /**
     * @param Tide $tide
     *
     * @return DeploymentStarted[]
     */
    private function getStartedDeployments(Tide $tide)
    {
        return $this->eventStore->findByTideUuidAndType($tide->getUuid(), DeploymentStarted::class);
    }
}
