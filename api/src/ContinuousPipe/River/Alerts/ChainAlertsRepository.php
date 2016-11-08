<?php

namespace ContinuousPipe\River\Alerts;

use ContinuousPipe\River\View\Flow;

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
    public function findByFlow(Flow $flow)
    {
        return array_reduce($this->repositoryCollection, function (array $alerts, AlertsRepository $alertsRepository) use ($flow) {
            return array_merge($alerts, $alertsRepository->findByFlow($flow));
        }, []);
    }
}
