<?php

namespace spec\ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Team\TeamUsageLimits;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HttpAuthenticatorClientSpec extends ObjectBehavior
{
    function let(
        Client $httpClient,
        SerializerInterface $serializer,
        ResponseInterface $response,
        StreamInterface $stream
    ) {
        $authenticatorUrl = 'http://localhost';
        $authenticationToken = '1234567890';
        $this->beConstructedWith($httpClient, $serializer, $authenticatorUrl, $authenticationToken);

        $this->initResponseStub($response, $stream);
    }

    function it_can_delete_team_by_slug(Client $httpClient, ResponseInterface $response)
    {
        $httpClient->delete('http://localhost/api/teams/team-name', Argument::cetera())->willReturn($response)->shouldBeCalled();

        $this->deleteTeamBySlug('team-name');
    }

    function it_throws_team_not_found_exception(Client $httpClient)
    {
        $request = new Request('delete', '/');
        $response = new Response(404);
        $notFoundException = new ClientException('Team not found.', $request, $response);

        $httpClient->delete(Argument::cetera())->willThrow($notFoundException);
        $this->shouldThrow('ContinuousPipe\Security\Team\TeamNotFound')->duringDeleteTeamBySlug('team-name');
    }

    function it_returns_team_limits(Client $httpClient, ResponseInterface $response, SerializerInterface $serializer, TeamUsageLimits $teamUsageLimits)
    {
        $serializer->deserialize(Argument::cetera())->willReturn($teamUsageLimits);
        $httpClient->get('http://localhost/api/teams/team-name/usage-limits', Argument::cetera())->willReturn($response)->shouldBeCalled();

        $this->findTeamUsageLimitsBySlug('team-name')->shouldReturn($teamUsageLimits);
    }

    private function initResponseStub(ResponseInterface $response, StreamInterface $stream)
    {
        $response->getBody()->willReturn($stream);
    }
}
