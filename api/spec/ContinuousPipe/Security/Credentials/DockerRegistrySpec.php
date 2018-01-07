<?php

namespace spec\ContinuousPipe\Security\Credentials;

use ContinuousPipe\Security\Credentials\DockerRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DockerRegistrySpec extends ObjectBehavior
{
    function it_returns_the_server_address()
    {
        $this->beConstructedWith('username', 'password', 'email', 'docker.io');

        $this->getServerAddress()->shouldReturn('docker.io');
    }

    function it_returns_the_server_address_from_full_address()
    {
        $this->beConstructedWith('username', 'password', 'email', null, 'docker.io/sroze/php-example');

        $this->getServerAddress()->shouldReturn('docker.io');
    }
}
