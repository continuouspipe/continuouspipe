<?php

namespace ContinuousPipe\River\EventListener\GitHub\PullRequestClosed;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Pipe\ClusterIdentifierNotFound;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
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
     * @param Client                    $client
     * @param TideRepository            $tideRepository
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param ClusterIdentifierResolver $clusterIdentifierResolver
     * @param LoggerInterface           $systemLogger
     */
    public function __construct(Client $client, TideRepository $tideRepository, EnvironmentNamingStrategy $environmentNamingStrategy, ClusterIdentifierResolver $clusterIdentifierResolver, LoggerInterface $systemLogger)
    {
        $this->client = $client;
        $this->tideRepository = $tideRepository;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->clusterIdentifierResolver = $clusterIdentifierResolver;
        $this->systemLogger = $systemLogger;
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
            } catch (ClusterIdentifierNotFound $e) {
                $this->systemLogger->error('Unable to resolve tide target provider', [
                    'exception' => $e,
                    'tide' => $tide,
                ]);

                continue;
            }

            $this->client->deleteEnvironment($target, $tide->getTeam(), $tide->getUser());
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
            $this->clusterIdentifierResolver->getClusterIdentifier($tide)
        );
    }
}
