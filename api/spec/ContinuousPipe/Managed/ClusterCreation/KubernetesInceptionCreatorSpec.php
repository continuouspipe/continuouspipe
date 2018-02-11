<?php

namespace spec\ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Managed\ClusterCreation\ClusterCreator;
use ContinuousPipe\Managed\ClusterCreation\KubernetesInceptionCreator;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use ContinuousPipe\Security\Team\Team;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class KubernetesInceptionCreatorSpec extends ObjectBehavior
{
    function let(RelativeFileSystem $fileSystem)
    {
        $this->beConstructedWith($fileSystem);
    }

    function it_is_a_cluster_creator()
    {
        $this->shouldImplement(ClusterCreator::class);
    }

    function it_supports_kinception_dsn()
    {
        $this->supports(new Team('slug', 'Name'), 'identifier', 'kinception://this')->shouldBe(true);
    }

    function it_creates_a_cluster_from_the_local_filesystem(RelativeFileSystem $fileSystem)
    {
        $fileSystem->exists('/var/run/secrets/kubernetes.io/serviceaccount/token')->willReturn(true);
        $fileSystem->getContents('/var/run/secrets/kubernetes.io/serviceaccount/token')->willReturn('123.qwerty.jwt');

        putenv('KUBERNETES_SERVICE_HOST=10.3.240.1');

        $cluster = $this->createForTeam(new Team('slug', 'Name'), 'identifier', 'kinception://this');
        $cluster->getAddress()->shouldBe('https://10.3.240.1');
        $cluster->getCredentials()->getToken()->shouldBe('123.qwerty.jwt');
    }
}
