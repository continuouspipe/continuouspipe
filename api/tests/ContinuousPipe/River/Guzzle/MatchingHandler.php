<?php

namespace ContinuousPipe\River\Guzzle;

use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MatchingHandler
{
    /**
     * Array of matchers. A matcher is an array like:
     *
     * [
     *     'match' => function(RequestInterface $request) : bool,
     *     'response' => ResponseInterface|\Exception|callable(RequestInterface $request, array $options)
     * ]
     *
     * @var array
     */
    private $matchers = [];

    public function __construct(array $matchers = null)
    {
        if (null === $matchers) {
            $matchers = [
                [
                    'match' => function (RequestInterface $request) {
                        return $request->getUri() == 'https://bitbucket.org/site/oauth2/access_token';
                    },
                    'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                        'access_token' => '1234567890',
                        'expires_in' => 3600,
                        'scopes' => 'email webhook',
                    ])),
                ],
            ];
        }

        $this->matchers = $matchers;
    }

    public function __invoke(RequestInterface $request, array $options)
    {
        $response = $this->getMatchingResponse($request);

        if (is_callable($response)) {
            $response = call_user_func($response, $request, $options);
        }

        $response = $response instanceof \Exception
            ? new RejectedPromise($response)
            : \GuzzleHttp\Promise\promise_for($response);

        return $response->then(
            function ($value) use ($request, $options) {
                $this->invokeStats($request, $options, $value);

                if (isset($options['sink'])) {
                    $contents = (string) $value->getBody();
                    $sink = $options['sink'];

                    if (is_resource($sink)) {
                        fwrite($sink, $contents);
                    } elseif (is_string($sink)) {
                        file_put_contents($sink, $contents);
                    } elseif ($sink instanceof \Psr\Http\Message\StreamInterface) {
                        $sink->write($contents);
                    }
                }

                return $value;
            },
            function ($reason) use ($request, $options) {
                $this->invokeStats($request, $options, null, $reason);

                return new RejectedPromise($reason);
            }
        );
    }

    public function unshiftMatcher(array $matcher)
    {
        array_unshift($this->matchers, $matcher);
    }

    public function pushMatcher(array $matcher)
    {
        $this->matchers[] = $matcher;
    }

    private function invokeStats(
        RequestInterface $request,
        array $options,
        ResponseInterface $response = null,
        $reason = null
    ) {
        if (isset($options['on_stats'])) {
            $stats = new TransferStats($request, $response, 0, $reason);
            call_user_func($options['on_stats'], $stats);
        }
    }

    private function getMatchingResponse(RequestInterface $request)
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher['match']($request)) {
                return $matcher['response'];
            }
        }

        var_dump($request->getMethod(), (string) $request->getUri());

        throw new \OutOfBoundsException('No matcher found');
    }
}
