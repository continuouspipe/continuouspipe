<?php

namespace spec\ContinuousPipe\River\CodeRepository\DockerCompose\Configuration;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PortIdentifierSpec extends ObjectBehavior
{
    function it_has_string_representation()
    {
        $this->beConstructedThrough('fromNameAndPort', ['nginx', 80]);

        $this->toString()->shouldReturn('nginx80');
    }

    function it_should_limit_the_identifier_length()
    {
        $this->beConstructedThrough('fromNameAndPort', ['elasticsearch', 9200]);

        $this->toString()->shouldHaveMaxLength(15);
    }

    function it_should_limit_the_identifier_length_with_long_name_and_port()
    {
        $this->beConstructedThrough('fromNameAndPort', ['asdfghjklqwertyuiop', 12345678901234567890]);

        $this->toString()->shouldHaveMaxLength(15);
    }

    function it_should_not_limit_the_identifier_length_when_name_is_short()
    {
        $this->beConstructedThrough('fromNameAndPort', ['0123456789', 12345]);

        $this->toString()->shouldHaveMaxLength(15);
        $this->toString()->shouldReturn('012345678912345');
    }

    public function getMatchers()
    {
        return [
            'haveMaxLength' => function ($subject, $maxLength) {
                return mb_strlen($subject) <= $maxLength;
            },
        ];
    }
}
