<?php

namespace ContinuousPipe\River\Alerts;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

class ChainAlertsRepository implements AlertsRepository
{
    /**
     * @var array|AlertsRepository[]
     */
    private $repositoryCollection;

    /**
     * @param AlertsRepository[] $repositoryCollection
     */
    public function __construct(array $repositoryCollection)
    {
        $this->repositoryCollection = $repositoryCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(FlatFlow $flow)
    {
        return array_reduce($this->repositoryCollection, function (array $alerts, AlertsRepository $alertsRepository) use ($flow) {
            return array_merge($alerts, $alertsRepository->findByFlow($flow));
        }, []);
    }
}
