<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\TraceableInstallationRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketAccount;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClient;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientException;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientFactory;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\Guzzle\MatchingHandler;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class BitBucketContext implements Context
{
    /**
     * @var MatchingHandler
     */
    private $bitBucketMatchingClientHandler;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var TraceableInstallationRepository
     */
    private $traceableInstallationRepository;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var BitBucketClientFactory
     */
    private $clientFactory;
    /**
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    private $response;
    /**
     * @var BitBucketClient|null
     */
    private $client;
    /**
     * @var \Exception|null
     */
    private $exception;

    public function __construct(
        MatchingHandler $bitBucketMatchingClientHandler,
        KernelInterface $kernel,
        TraceableInstallationRepository $traceableInstallationRepository,
        SerializerInterface $serializer,
        BitBucketClientFactory $clientFactory
    ) {
        $this->bitBucketMatchingClientHandler = $bitBucketMatchingClientHandler;
        $this->kernel = $kernel;
        $this->traceableInstallationRepository = $traceableInstallationRepository;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
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

    /**
     * @When the add-on :clientKey is installed for the user account :principalUsername
     */
    public function theAddOnIsInstalledForTheUserAccount($clientKey, $principalUsername)
    {
        $addon = $this->createAddonArray($clientKey, $principalUsername);

        $this->response = $this->kernel->handle(Request::create(
            '/connect/service/bitbucket/addon/installed',
            'POST',
            [], [], [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($addon)
        ));

        if ($this->response->getStatusCode() != 204) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code 204, found %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then the installation should be saved
     */
    public function theInstallationShouldBeSaved()
    {
        $saved = $this->traceableInstallationRepository->getSaved();

        if (count($saved) == 0) {
            throw new \RuntimeException('Found 0 saved installation');
        }
    }

    /**
     * @Given there is the add-on :clientKey installed for the user account :principalUsername
     */
    public function thereIsTheAddOnInstalledForTheUserAccount($clientKey, $principalUsername)
    {
        $addon = $this->createAddonArray($clientKey, $principalUsername);
        $installation = $this->serializer->deserialize(
            json_encode($addon),
            Installation::class,
            'json'
        );

        $this->traceableInstallationRepository->save($installation);
    }

    /**
     * @Given there is the add-on installed for the BitBucket repository :name owned by user :username
     */
    public function thereIsTheAddOnInstalledForTheBitbucketRepositoryOwnedByUser($name, $username)
    {
        $addon = $this->createAddonArray('test-client-key', $username);
        $installation = $this->serializer->deserialize(
            json_encode($addon),
            Installation::class,
            'json'
        );

        $this->traceableInstallationRepository->save($installation);
    }

    /**
     * @Given there is a :path file in my BitBucket repository that contains:
     */
    public function thereIsAFileInMyBitbucketRepositoryThatContains($path, PyStringNode $string)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($path) {
                if ($request->getMethod() != 'GET') {
                    return false;
                }

                if (!preg_match('#^https\:\/\/api\.bitbucket\.org\/1\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/src\/([a-z0-9_-]+)\/(?<path>.+)$#i', (string) $request->getUri(), $matches)) {
                    return false;
                }

                return $matches['path'] == $path;
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'node' => '001823eef762',
                'path' => $path,
                'data' => $string->getRaw(),
                'size' => strlen($string->getRaw()),
            ])),
        ]);
    }

    /**
     * @When I create a client for the BitBucket repository :repositoryOwnerAndName
     */
    public function iCreateAClientForTheBitbucketRepository($repositoryOwnerAndName)
    {
        list($ownerUsername, $repositoryName) = explode('/', $repositoryOwnerAndName);

        try {
            $this->client = $this->clientFactory->createForCodeRepository(
                new BitBucketCodeRepository(
                    Uuid::uuid4()->toString(),
                    new BitBucketAccount(
                        Uuid::uuid4()->toString(),
                        $ownerUsername,
                        'user'
                    ),
                    $repositoryName,
                    'https://api.bitbucket.org/2.0/repositories/' . $ownerUsername . '/' . $repositoryName,
                    'master',
                    true
                )
            );
        } catch (BitBucketClientException $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then the client should use the JWT token of the addon :clientKey
     */
    public function theClientShouldUseTheJwtTokenOfTheAddon($clientKey)
    {
        if (null === $this->client) {
            throw new \RuntimeException('Client has not been created');
        }

        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($clientKey) {
                if ($request->getUri() != 'https://api.bitbucket.org/2.0/repositories/owner/repository/refs/branches/branch') {
                    return false;
                }

                list ($method, $token) = explode(' ', $request->getHeader('Authorization')[0]);
                list ($header, $payload, $signature) = explode('.', $token);

                $payload = json_decode(base64_decode($payload), true);

                return $payload['sub'] == $clientKey;
            },
            'response' => function(RequestInterface $request) {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'type' => 'branch',
                    'name' => 'master',
                    'target' => [
                        'hash' => sha1('sha1'),
                    ],
                ]));
            },
        ]);

        $this->client->getReference('owner', 'repository', 'branch');
    }

    /**
     * @Then the client should not be created because of the missing add-on installation
     */
    public function theClientShouldNotBeCreatedBecauseOfTheMissingAddOnInstallation()
    {
        if (null !== $this->client) {
            throw new \RuntimeException('Client has been created');
        }

        if (null === $this->exception) {
            throw new \RuntimeException('No reason found for no client');
        }
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
                        'uuid' => Uuid::uuid4()->toString(),
                        'username' => $username,
                        'type' => 'user',
                        'display_name' => $username,
                    ],
                ];
            }, $table->getHash()),
        ]));
    }

    private function createAddonArray(string $clientKey, string $principalUsername): array
    {
        $addon = \GuzzleHttp\json_decode(file_get_contents(__DIR__ . '/../bitbucket/fixtures/addon-installed.json'), true);
        $addon['clientKey'] = $clientKey;
        $addon['principal']['type'] = 'user';
        $addon['principal']['username'] = $principalUsername;

        return $addon;
    }
}
