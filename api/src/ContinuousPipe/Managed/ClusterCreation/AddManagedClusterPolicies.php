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

    /**
     * @var ManagedCloudFlareCredentials
     */
    private $managedCloudFlareCredentials;

    /**
     * @var string|null
     */
    private $rbacClusterRole;

    /**
     * @param ClusterCreator $decoratedCreator
     * @param array $managedCloudFlareCredentials
     */
    public function __construct(
        ClusterCreator $decoratedCreator,
        array $managedCloudFlareCredentials,
        string $rbacClusterRole = null
    ) {
        $this->decoratedCreator = $decoratedCreator;
        $this->managedCloudFlareCredentials = ManagedCloudFlareCredentials::fromArray($managedCloudFlareCredentials);
        $this->rbacClusterRole = $rbacClusterRole;
    }

    /**
     * {@inheritdoc}
     */
    public function createForTeam(Team $team, string $clusterIdentifier): Cluster
    {
        return $this->decoratedCreator->createForTeam($team, $clusterIdentifier)->withPolicies(
            $this->generatePolicies($team)
        );
    }

    private function generatePolicies(Team $team) : array
    {
        $teamHostSuffix = $this->getTeamHostSuffix($team);
        $policies = [
            new Cluster\ClusterPolicy('default'),
            new Cluster\ClusterPolicy('managed'),
            new Cluster\ClusterPolicy('resources', [
                // Default requests
                'default-requests' => true,
                'default-cpu-request' => '25m',
                'default-memory-request' => '256Mi',

                // Default limits
                'default-limits' => true,
                'default-cpu-limit' => '500m',
                'default-memory-limit' => '256Mi',

                // Maximum requests
                'max-requests' => true,
                'max-cpu-request' => '2',
                'max-memory-request' => '4Gi',

                // Maximum limits
                'max-limits' => true,
                'max-cpu-limit' => '2',
                'max-memory-limit' => '4Gi',
            ]),
            new Cluster\ClusterPolicy('endpoint', [
                // Endpoint enforcements
                'type' => 'ingress',
                'class' => 'nginx',

                'default-host-suffix' => $teamHostSuffix,
                'host-rules' => [
                    ['domain' => 'continuouspipe.net', 'suffix' => $teamHostSuffix],
                ],

                // Default
                'cloudflare-by-default' => true,
                'ssl-certificate-defaults' => true,
                'cloudflare-proxied-by-default' => true,
            ], [
                // CloudFlare
                'cloudflare-zone-identifier' => $this->managedCloudFlareCredentials->getZoneIdentifier(),
                'cloudflare-email' => $this->managedCloudFlareCredentials->getEmail(),
                'cloudflare-api-key' => $this->managedCloudFlareCredentials->getApiKey(),

                // SSL certificates
                'ssl-certificate-key' => 'automatic',
                'ssl-certificate-cert' => 'automatic',
            ])
        ];

        if (null !== $this->rbacClusterRole) {
            $policies[] = new Cluster\ClusterPolicy('rbac', [
                'cluster-role' => $this->rbacClusterRole,
            ]);
        }

        return $policies;
    }

    private function getTeamHostSuffix(Team $team)
    {
        return '-'.Hashifier::maxLength($team->getSlug(), 10, 5).'.'.$this->managedCloudFlareCredentials->getDomainName();
    }
}
