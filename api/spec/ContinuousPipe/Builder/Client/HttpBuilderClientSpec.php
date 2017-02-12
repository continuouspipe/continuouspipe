<?php

namespace spec\ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\Builder\Client\BuilderException;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HttpBuilderClientSpec extends ObjectBehavior
{
    function let(ClientInterface $client, SerializerInterface $serializer, JWTManagerInterface $jwtManager)
    {
        $this->beConstructedWith(
            $client,
            $serializer,
            $jwtManager,
            'https://example.com'
        );
    }

    function it_is_a_builder_client()
    {
        $this->shouldHaveType(BuilderClient::class);
    }

    function it_throws_the_message_from_the_request(ClientInterface $client, BuildRequest $buildRequest, User $user)
    {
        $requestException = new RequestException(
            'Client error',
            new Request('POST', 'https://example.com/build'),
            new Response(400, ['Content-Type' => 'application/json'], \GuzzleHttp\json_encode([
                'error' => [
                    'message' => 'No registry credentials found'
                ]
            ]))
        );

        $client->request('POST', 'https://example.com/build', Argument::type('array'))->willThrow($requestException);

        $this->shouldThrow(new BuilderException(
            'No registry credentials found',
            400,
            $requestException
        ))->duringBuild($buildRequest, $user);
    }
}
