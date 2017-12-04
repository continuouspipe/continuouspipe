<?php

namespace spec\ContinuousPipe\Security\Encryption\GoogleKms;

use ContinuousPipe\Security\Encryption\GoogleKms\GoogleKmsClientResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GoogleKmsClientResolverSpec extends ObjectBehavior
{
    function it_returns_a_google_kms_client(\Google_Client $googleClient)
    {
        $this->beConstructedWith('/path/of/service/account/file.json');

        $googleClient->setAuthConfig('/path/of/service/account/file.json')->shouldBeCalled();
        $googleClient->setScopes([
            'https://www.googleapis.com/auth/cloud-platform'
        ])->shouldBeCalled();

        $this->get($googleClient)->shouldBeAnInstanceOf(\Google_Service_CloudKMS::class);
    }
}
