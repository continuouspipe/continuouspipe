<?php

namespace spec\ContinuousPipe\Pipe\Client;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvironmentDeploymentRequestSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('envName', 'providerName', 'dockerComposeContents');
    }

    function it_exposes_the_name_of_the_environment()
    {
        $this->getEnvironmentName()->shouldReturn('envName');
    }

    function it_exposes_the_provider_name()
    {
        $this->getProviderName()->shouldReturn('providerName');
    }

    function it_exposes_the_contents_of_docker_compose_file()
    {
        $this->getDockerComposeContents()->shouldReturn('dockerComposeContents');
    }
}
