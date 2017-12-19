<?php

namespace spec\ContinuousPipe\River\Tests\View;

use ContinuousPipe\River\Tests\View\PredictableTimeResolver;
use ContinuousPipe\River\View\TimeResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PredictableTimeResolverSpec extends ObjectBehavior
{
    function let(TimeResolver $timeResolver)
    {
        $this->beConstructedWith($timeResolver);
    }

    function it_returns_the_decorated_resolver_time_by_default(TimeResolver $timeResolver)
    {
        $date = new \DateTime();

        $timeResolver->resolve()->willReturn($date);

        $this->resolve()->shouldBe($date);
    }

    function it_returns_the_predicated_datetime_if_set()
    {
        $current = new \DateTime('yesterday');
        $this->setCurrent($current);

        $this->resolve()->shouldBeLike($current);
    }

    function it_returns_a_non_updatable_copy_of_the_date()
    {
        $current = new \DateTime('yesterday');

        $this->setCurrent($current);
        $this->resolve()->add(new \DateInterval('P1D'));
        $this->resolve()->shouldBeLike(new \DateTime('yesterday'));
    }
}
