<?php

namespace ContinuousPipe\Alerts;

use ContinuousPipe\Security\Team\Team;

class CollectionAlertFinder implements AlertFinder
{
    /**
     * @var array|AlertFinder[]
     */
    private $finderCollection;

    /**
     * @param AlertFinder[] $finderCollection
     */
    public function __construct(array $finderCollection)
    {
        $this->finderCollection = $finderCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team) : array
    {
        return array_reduce($this->finderCollection, function (array $alerts, AlertFinder $finder) use ($team) {
            return array_merge($alerts, $finder->findByTeam($team));
        }, []);
    }
}
