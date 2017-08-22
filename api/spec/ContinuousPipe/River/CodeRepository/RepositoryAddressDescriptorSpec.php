<?php

namespace spec\ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository\RepositoryAddressDescriptor;
use ContinuousPipe\River\CodeRepository\RepositoryDescription;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepositoryAddressDescriptorSpec extends ObjectBehavior
{
    function it_returns_the_description_from_a_github_address()
    {
        $this->getDescription('https://github.com/continuouspipe/dockerfiles')->shouldBeLike(new RepositoryDescription(
            'continuouspipe',
            'dockerfiles'
        ));
    }

    function it_returns_the_description_from_a_github_address_with_the_dot_git()
    {
        $this->getDescription('https://github.com/continuouspipe/dockerfiles.git')->shouldBeLike(new RepositoryDescription(
            'continuouspipe',
            'dockerfiles'
        ));
    }

    function it_returns_the_description_from_a_github_api_address()
    {
        $this->getDescription('https://api.github.com/repos/inviqa/ft')->shouldBeLike(new RepositoryDescription(
            'inviqa',
            'ft'
        ));
    }

    function it_returns_the_description_of_a_repository_with_dots()
    {
        $this->getDescription('https://api.github.com/repos/inviqa/platform.cms.intermediair')->shouldBeLike(new RepositoryDescription(
            'inviqa',
            'platform.cms.intermediair'
        ));
    }

    function it_returns_the_description_of_a_repository_with_dots_and_dot_git()
    {
        $this->getDescription('https://api.github.com/repos/inviqa/platform.cms.intermediair.git')->shouldBeLike(new RepositoryDescription(
            'inviqa',
            'platform.cms.intermediair'
        ));
    }
}
