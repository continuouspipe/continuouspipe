<?php

namespace spec\ContinuousPipe\Pipe\Client;

use PhpSpec\ObjectBehavior;

class EnvironmentDeploymentRequestSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('envName', 'providerName', 'dockerComposeContents');
    }

    public function it_exposes_the_name_of_the_environment()
    {
        $this->getEnvironmentName()->shouldReturn('envName');
    }

    public function it_exposes_the_provider_name()
    {
        $this->getProviderName()->shouldReturn('providerName');
    }

    public function it_exposes_the_contents_of_docker_compose_file()
    {
        $this->getDockerComposeContents()->shouldReturn('dockerComposeContents');
    }
}
