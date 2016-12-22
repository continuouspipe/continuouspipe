<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\River\Guzzle\MatchingHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class BitBucketContext implements Context
{
    /**
     * @var MatchingHandler
     */
    private $bitBucketMatchingClientHandler;

    public function __construct(MatchingHandler $bitBucketMatchingClientHandler)
    {
        $this->bitBucketMatchingClientHandler = $bitBucketMatchingClientHandler;
    }

    /**
     * @Given the BitBucket user :username have the following repositories:
     */
    public function theBitbucketUserHaveTheFollowingRepositories($username, TableNode $table)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($username) {
                return $request->getMethod() == 'GET' && $request->getUri() == 'https://api.bitbucket.org/2.0/repositories/'.$username;
            },
            'response' => $this->createRepositoriesResponse($username, $table),
        ]);
    }

    /**
     * @Given the BitBucket user :username belong to the following teams:
     */
    public function theBitbucketUserBelongToTheFollowingTeams($username, TableNode $table)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($username) {
                return $request->getMethod() == 'GET' && $request->getUri() == 'https://api.bitbucket.org/2.0/teams?role=contributor';
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'pagelen' => 10,
                'page' => 1,
                'size' => 1,
                'values' => array_map(function(array $team) use ($username) {
                    return [
                        'username' => $team['username'],
                        'links' => [
                            'self' => [
                                'href' => 'https://api.bitbucket.org/2.0/teams/'.$team['username'],
                            ],
                            'avatar' => [
                                'href' => 'https://api.bitbucket.org/2.0/teams/'.$team['username'].'/avatar/30/',
                            ],
                        ],
                    ];
                }, $table->getHash()),
            ])),
        ]);
    }

    /**
     * @Given the BitBucket team :team have the following repositories:
     */
    public function theBitbucketTeamHaveTheFollowingRepositories($team, TableNode $table)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($team) {
                return $request->getMethod() == 'GET' && $request->getUri() == 'https://api.bitbucket.org/2.0/teams/'.$team.'/repositories';
            },
            'response' => $this->createRepositoriesResponse($team, $table),
        ]);
    }

    private function createRepositoriesResponse(string $username, TableNode $table): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'pagelen' => 10,
            'page' => 1,
            'size' => 1,
            'values' => array_map(function (array $repository) use ($username) {
                return [
                    'scm' => 'git',
                    'name' => $repository['name'],
                    'uuid' => $repository['uuid'],
                    'links' => [
                        'self' => [
                            'href' => 'https://api.bitbucket.org/2.0/repositories/' . $username . '/' . $repository['name'],
                        ],
                    ],
                    'is_private' => true,
                    'owner' => [
                        'username' => $username,
                    ],
                ];
            }, $table->getHash()),
        ]));
    }
}
