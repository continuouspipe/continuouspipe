<?php

namespace spec\ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Managed\ClusterCreation\ClusterCreator;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreatorAdapter;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use ContinuousPipe\Security\Team\Team;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClusterCreatorAdapterSpec extends ObjectBehavior
{
    function let(ClusterCreator $c1, ClusterCreator $c2)
    {
        $this->beConstructedWith([$c1, $c2]);
    }

    function it_is_a_cluster_creator()
    {
        $this->shouldImplement(ClusterCreator::class);
    }

    function it_uses_the_creator_that_supports_the_dsn(ClusterCreator $c1, ClusterCreator $c2)
    {
        $c1->supports($team = new Team('slug', 'Name'), $identifier = 'identifier', $dsn = 'dsn')->willReturn(false);
        $c2->supports($team, $identifier, $dsn)->willReturn(true);
        $c2->createForTeam($team, $identifier, $dsn)->willReturn($cluster = new Kubernetes($identifier, 'address', 'version'));

        $this->createForTeam($team, $identifier, $dsn)->shouldBe($cluster);
    }
}
