<?php

namespace spec\ContinuousPipe\River;

use ContinuousPipe\River\CodeRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CodeReferenceSpec extends ObjectBehavior
{
    function let(CodeRepository $codeRepository)
    {
        $this->beConstructedWith($codeRepository, 'master');
    }

    function it_exposes_the_reference()
    {
        $this->getReference()->shouldReturn('master');
    }

    function it_exposes_the_code_repository(CodeRepository $codeRepository)
    {
        $this->getRepository()->shouldReturn($codeRepository);
    }
}
