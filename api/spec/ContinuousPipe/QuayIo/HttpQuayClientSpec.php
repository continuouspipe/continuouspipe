<?php

namespace spec\ContinuousPipe\QuayIo;

use ContinuousPipe\QuayIo\HttpQuayClient;
use ContinuousPipe\QuayIo\QuayClient;
use ContinuousPipe\QuayIo\RobotAccount;
use ContinuousPipe\QuayIo\RobotAlreadyExists;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HttpQuayClientSpec extends ObjectBehavior
{
    function let(ClientInterface $httpClient)
    {
        $this->beConstructedWith($httpClient, 'foo', 'token');
    }

    function it_is_a_quay_client()
    {
        $this->shouldImplement(QuayClient::class);
    }

    function it_returns_a_robot_if_already_exists_exception(ClientInterface $httpClient)
    {
        $httpClient->request('put', 'https://quay.io/api/v1/organization/foo/robots/bar', Argument::any())->willThrow(
            RequestException::create(
                new Request('put', 'https://quay.io/api/v1/organization/foo/robots/bar'),
                new Response(400, ['Content-Type' => 'application/json'], \GuzzleHttp\json_encode([
                    'message' => 'Existing robot with name: bar',
                ]))
            )
        );

        $httpClient->request('get', 'https://quay.io/api/v1/organization/foo/robots/bar', Argument::any())->willReturn(
            new Response(200, ['Content-Type' => 'application/json'], \GuzzleHttp\json_encode([
                'name' => 'organization+bar',
                'token' => 'token',
            ]))
        );

        $this->createRobotAccount('bar')->shouldBeLike(new RobotAccount(
            'organization+bar',
            'token',
            'robot+bar@continuouspipe.net'
        ));
    }
}
