<?php

namespace ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

class AddManagedClusterPolicies implements ClusterCreator
{
    /**
     * @var ClusterCreator
     */
    private $decoratedCreator;

    public function __construct(
        ClusterCreator $decoratedCreator
    ) {
        $this->decoratedCreator = $decoratedCreator;
    }

    /**
     * {@inheritdoc}
     */
    public function createForTeam(Team $team, string $clusterIdentifier, string $dsn): Cluster
    {
        return $this->decoratedCreator->createForTeam($team, $clusterIdentifier, $dsn)->withPolicies(
            $this->generatePolicies($team, $dsn)
        );
    }

    private function generatePolicies(Team $team, string $dsn) : array
    {
        $parsedDsn = parse_url($dsn);

        $policies = [
            new Cluster\ClusterPolicy('default'),
            new Cluster\ClusterPolicy('managed'),
        ];

        if (isset($parsedDsn['query'])) {
            parse_str($parsedDsn['query'], $query);

            if (isset($query['policies']) && is_array($query['policies'])) {
                foreach ($query['policies'] as $name => $configuration) {
                    $policies[] = new Cluster\ClusterPolicy(
                        $name,
                        is_string($configuration) ? \GuzzleHttp\json_decode($configuration, true) : $configuration
                    );
                }
            }
        }

        return $policies;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Team $team, string $clusterIdentifier, string $dsn): bool
    {
        return $this->decoratedCreator->supports($team, $clusterIdentifier, $dsn);
    }
}
