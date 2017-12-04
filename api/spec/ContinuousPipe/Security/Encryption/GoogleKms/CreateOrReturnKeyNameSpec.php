<?php

namespace spec\ContinuousPipe\Security\Encryption\GoogleKms;

use ContinuousPipe\Security\Encryption\GoogleKms\CreateOrReturnKeyName;
use ContinuousPipe\Security\Encryption\GoogleKms\GoogleKmsClientResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class CreateOrReturnKeyNameSpec extends ObjectBehavior
{
    function let(GoogleKmsClientResolver $clientResolver, \Google_Client $client)
    {
        $client->getLogger()->willReturn(new NullLogger());
        $client->shouldDefer()->willReturn(false);

        $this->beConstructedWith($clientResolver, 'project-id', 'location', 'key-ring');
    }

    function it_gets_the_key_from_the_api(GoogleKmsClientResolver $clientResolver, \Google_Client $client)
    {
        $client->execute(Argument::that(function(\GuzzleHttp\Psr7\Request $request) {
            return $request->getMethod() == 'GET' && $request->getUri()->getPath() == '/v1/projects/project-id/locations/location/keyRings/key-ring/cryptoKeys/namespace';
        }), 'Google_Service_CloudKMS_CryptoKey')->willReturn(new \Google_Service_CloudKMS_CryptoKey([
            'name' => 'full/key/name'
        ]));

        $clientResolver->get()->willReturn(new \Google_Service_CloudKMS($client->getWrappedObject()));

        $this->keyName('namespace')->shouldBeLike('full/key/name');
    }

    function it_creates_an_api_key_if_it_do_not_exists(GoogleKmsClientResolver $clientResolver, \Google_Client $client)
    {
        $client->execute(Argument::that(function(\GuzzleHttp\Psr7\Request $request) {
            return $request->getMethod() == 'GET' && $request->getUri()->getPath() == '/v1/projects/project-id/locations/location/keyRings/key-ring/cryptoKeys/namespace';
        }), 'Google_Service_CloudKMS_CryptoKey')->willThrow(new \Google_Service_Exception('Get not found', 404));

        $client->execute(Argument::that(function(\GuzzleHttp\Psr7\Request $request) {
            return $request->getMethod() == 'POST' && $request->getUri()->getPath() == '/v1/projects/project-id/locations/location/keyRings/key-ring/cryptoKeys';
        }), 'Google_Service_CloudKMS_CryptoKey')->willReturn(new \Google_Service_CloudKMS_CryptoKey([
            'name' => 'full/key/name'
        ]))->shouldBeCalled();

        $clientResolver->get()->willReturn(new \Google_Service_CloudKMS($client->getWrappedObject()));

        $this->keyName('namespace')->shouldBeLike('full/key/name');
    }
}
