<?php

namespace spec\ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Managed\ClusterCreation\AddManagedClusterPolicies;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreator;
use ContinuousPipe\Security\Credentials\Cluster\ClusterPolicy;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use ContinuousPipe\Security\Team\Team;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddManagedClusterPoliciesSpec extends ObjectBehavior
{
    function let(ClusterCreator $decorated)
    {
        $this->beConstructedWith($decorated);
    }

    function it_is_a_cluster_creator()
    {
        $this->shouldImplement(ClusterCreator::class);
    }

    function it_adds_a_set_of_policies_by_default(ClusterCreator $decorated)
    {
        $cluster = new Kubernetes('identifier', 'address', 'version');
        $team = new Team('slug', 'Name');

        $decorated->createForTeam($team, 'identifier', $dsn = 'void:///')->willReturn($cluster);

        $cluster = $this->createForTeam($team, 'identifier', $dsn);
        $cluster->getPolicies()->shouldBeLike([
            new ClusterPolicy('default'),
            new ClusterPolicy('managed'),
        ]);
    }

    function it_can_add_more_policies_using_parameters(ClusterCreator $decorated)
    {
        $cluster = new Kubernetes('identifier', 'address', 'version');
        $team = new Team('slug', 'Name');

        $decorated->createForTeam(
            $team,
            $clusterIdentifier = 'identifier',
            $dsn = 'void://host/?policies[network]='.\GuzzleHttp\json_encode($networkPolicy = [
                    'rules' => [
                        ['type' => 'allow-current-namespace'],
                        ['type' => 'allow-from-namespace', 'label-key' => 'name', 'label-value' => 'ingress-nginx'],
                    ],
                ]).'&policies[rbac][cluster-role]=my-role'
        )->willReturn($cluster);

        $cluster = $this->createForTeam($team, $clusterIdentifier, $dsn);
        $cluster->getPolicies()->shouldBeLike([
            new ClusterPolicy('default'),
            new ClusterPolicy('managed'),
            new ClusterPolicy('network', $networkPolicy),
            new ClusterPolicy('rbac', [
                'cluster-role' => 'my-role',
            ])
        ]);
    }
}
