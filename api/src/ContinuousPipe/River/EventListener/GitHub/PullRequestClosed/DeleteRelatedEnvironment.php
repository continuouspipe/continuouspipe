<?php

namespace ContinuousPipe\River\EventListener\GitHub\PullRequestClosed;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Pipe\ProviderNameResolver;
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
     * @var ProviderNameResolver
     */
    private $providerNameResolver;

    /**
     * @param Client                    $client
     * @param TideRepository            $tideRepository
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param ProviderNameResolver      $providerNameResolver
     */
    public function __construct(Client $client, TideRepository $tideRepository, EnvironmentNamingStrategy $environmentNamingStrategy, ProviderNameResolver $providerNameResolver)
    {
        $this->client = $client;
        $this->tideRepository = $tideRepository;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->providerNameResolver = $providerNameResolver;
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
            $this->providerNameResolver->getProviderName($tide->getFlow())
        );
    }
}
