<?php

namespace spec\ContinuousPipe\River\ClusterPolicies\Resources;

use ContinuousPipe\River\Managed\Resources\Calculation\ResourceConverter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResourceConverterSpec extends ObjectBehavior
{
    function it_returns_the_amount_of_cpus()
    {
        $this::resourceToNumber('10m')->shouldReturn(0.01);
        $this::resourceToNumber('1')->shouldReturn(1.0);
        $this::resourceToNumber('2')->shouldReturn(2.0);
        $this::resourceToNumber('500m')->shouldReturn(0.5);
    }

    function it_returns_the_megabytes_of_memory()
    {
        $this::resourceToNumber('100Mi')->shouldReturn(100.0);
        $this::resourceToNumber('2048Mi')->shouldReturn(2048.0);
        $this::resourceToNumber('100Gi')->shouldReturn(100000.0);
    }
}
