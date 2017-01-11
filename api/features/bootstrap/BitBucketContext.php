<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\TraceableInstallationRepository;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketAccount;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClient;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientException;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientFactory;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\Guzzle\MatchingHandler;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class BitBucketContext implements CodeRepositoryContext
{
    /**
     * @var \FlowContext
     */
    private $flowContext;
    /**
     * @var \TideContext
     */
    private $tideContext;
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
    /**
     * @var History
     */
    private $guzzleHistory;
    /**
     * @var InMemoryCodeRepositoryRepository
     */
    private $inMemoryCodeRepositoryRepository;

    public function __construct(
        MatchingHandler $bitBucketMatchingClientHandler,
        KernelInterface $kernel,
        TraceableInstallationRepository $traceableInstallationRepository,
        SerializerInterface $serializer,
        BitBucketClientFactory $clientFactory,
        History $guzzleHistory,
        InMemoryCodeRepositoryRepository $inMemoryCodeRepositoryRepository
    ) {
        $this->bitBucketMatchingClientHandler = $bitBucketMatchingClientHandler;
        $this->kernel = $kernel;
        $this->traceableInstallationRepository = $traceableInstallationRepository;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
        $this->guzzleHistory = $guzzleHistory;
        $this->inMemoryCodeRepositoryRepository = $inMemoryCodeRepositoryRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->flowContext = $scope->getEnvironment()->getContext('FlowContext');
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
    }

    /**
     * @Given there is a repository identifier :identifier
     */
    public function thereIsARepositoryIdentified($identifier = null): CodeRepository
    {
        $username = 'sroze';
        $name = 'php-example';

        $repository = new CodeRepository\BitBucket\BitBucketCodeRepository(
            Uuid::uuid5(Uuid::NIL, $identifier)->toString(),
            new CodeRepository\BitBucket\BitBucketAccount(
                '{UUID}',
                $username,
                'user'
            ),
            $name,
            'https://api.bitbucket.org/2.0/repositories/'.$username.'/'.$name,
            'master',
            true
        );

        $this->inMemoryCodeRepositoryRepository->add($repository);

        $this->thereIsTheAddOnInstalledForTheBitbucketRepositoryOwnedByUser($name, $username);

        return $repository;
    }

    /**
     * @Given the created comment will have the ID :identifier
     */
    public function theCreatedCommentWillHaveTheId($identifier)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) {
                return $request->getMethod() == 'POST' &&
                    preg_match('#^https\:\/\/api\.bitbucket\.org\/1\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/pullrequests\/([a-z0-9_-]+)\/comments$#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'comment_id' => $identifier,
            ])),
        ]);
    }

    /**
     * @Given the pull-request #:identifier contains the tide-related commit
     */
    public function thePullRequestContainsTheTideRelatedCommit($identifier)
    {
        $tide = $this->tideContext->getCurrentTide();

        $pullRequests = \GuzzleHttp\json_decode($this->readFixture('list-of-opened-pull-requests.json'), true);
        $pullRequests['values'][0]['id'] = (int) $identifier;
        $pullRequests['values'][0]['source']['commit']['hash'] = $tide->getCodeReference()->getCommitSha();
        $pullRequests['values'][0]['source']['branch']['name'] = $tide->getCodeReference()->getBranch();

        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) {
                return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.bitbucket\.org\/2\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/pullrequests\?state\=OPEN$#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode($pullRequests)),
        ]);
    }

    /**
     * @Then the addresses of the environment should be commented on the pull-request
     */
    public function theAddressesOfTheEnvironmentShouldBeCommentedOnThePullRequest()
    {
        foreach ($this->guzzleHistory as $request) {
            /** @var \GuzzleHttp\Psr7\Request $request */
            if ($request->getMethod() != 'POST' ||
                !preg_match('#^https\:\/\/api\.bitbucket\.org\/1\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/pullrequests\/([a-z0-9_-]+)\/comments$#i', (string) $request->getUri())) {
                continue;
            }

            return $request;
        }

        throw new \RuntimeException('No matching request found');
    }

    /**
     * @When a pull-request is created from branch :branch with head commit :sha1
     */
    public function aPullRequestIsCreatedFromBranchWithHeadCommit($branch, $sha1)
    {
        $this->thePullRequestContainsTheTideRelatedCommit(1234);

        $body = $this->webhookBoilerplate('webhook/pull-request-created.json');
        $body['data']['pullrequest']['source']['repository'] = $body['data']['repository'];
        $body['data']['pullrequest']['source']['commit']['hash'] = $sha1;
        $body['data']['pullrequest']['source']['branch']['name'] = $branch;

        $this->sendWebhook($body);
    }

    /**
     * @Then the comment :identifier should have been deleted
     */
    public function theCommentShouldHaveBeenDeleted($identifier)
    {
        $uris = [];

        foreach ($this->guzzleHistory as $request) {
            /** @var \GuzzleHttp\Psr7\Request $request */
            if ($request->getMethod() != 'DELETE' ||
                !preg_match('#^https\:\/\/api\.bitbucket\.org\/1\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/pullrequests\/([a-z0-9_-]+)\/comments/'.$identifier.'#i', (string) $request->getUri())) {
                continue;
            }

            $uris[] = (string) $request->getUri();

            return true;
        }

        throw new \RuntimeException(sprintf(
            'No matching request found in: %s',
            implode(', ', $uris)
        ));
    }

    /**
     * @Then the address :address should be commented on the pull-request
     */
    public function theAddressShouldBeCommentedOnThePullRequest($address)
    {
        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $this->theAddressesOfTheEnvironmentShouldBeCommentedOnThePullRequest();
        $body = $request->getBody();
        $body->rewind();

        parse_str($body->getContents(), $comment);
        $content = $comment['content'];

        if (strpos($content, $address) === false) {
            throw new \RuntimeException('Address is not found in comment');
        }
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
     * @Given the BitBucket build status request will succeed
     */
    public function theBitbucketBuildStatusRequestWillSucceed()
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) {
                return $request->getMethod() == 'POST' &&
                       preg_match('#^https\:\/\/api\.bitbucket\.org\/2\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/commit\/(?<sha1>[a-z0-9_-]+)\/statuses\/build$#i', (string) $request->getUri());
            },
            'response' => new Response(),
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
     * @When I push the commit :sha1 to the branch :branch of the BitBucket repository :repositoryName owned by :ownerType :ownerUsername
     */
    public function iPushTheCommitToTheBranchOfTheBitbucketRepositoryOwnedByUser($sha1, $branch, $repositoryName, $ownerType, $ownerUsername)
    {
        $body = \GuzzleHttp\json_decode($this->readFixture('webhook/pushed-in-branch.json'), true);
        $body['data']['repository']['uuid'] = Uuid::uuid5(Uuid::NIL, $repositoryName)->toString();
        $body['data']['repository']['name'] = $repositoryName;
        $body['data']['repository']['owner']['type'] = strtolower($ownerType);
        $body['data']['repository']['owner']['username'] = $ownerUsername;
        $body['data']['push']['changes'][0]['new']['type'] = 'branch';
        $body['data']['push']['changes'][0]['new']['name'] = $branch;
        $body['data']['push']['changes'][0]['new']['target']['hash'] = $sha1;

        $this->sendWebhook($body);
    }

    /**
     * @When the branch :branch with head :sha1 is deleted
     */
    public function theBranchWithHeadIsDeleted($branch, $sha1)
    {
        $body = $this->webhookBoilerplate('webhook/push-branch-deleted.json');
        $body['data']['push']['changes'][0]['old']['type'] = 'branch';
        $body['data']['push']['changes'][0]['old']['name'] = $branch;
        $body['data']['push']['changes'][0]['old']['target']['hash'] = $sha1;

        $this->sendWebhook($body);
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

        $this->assertResponseStatus(204);
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
     * @Given there is the add-on installed for the BitBucket repository :repositoryName owned by user :ownerUsername
     */
    public function thereIsTheAddOnInstalledForTheBitbucketRepositoryOwnedByUser($repositoryName, $ownerUsername)
    {
        $addon = $this->createAddonArray('test-client-key', $ownerUsername);
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
     * @Then a commit status should have been sent
     */
    public function aCommitStatusShouldHaveBeenSent()
    {
        foreach ($this->guzzleHistory as $request) {
            /** @var \GuzzleHttp\Psr7\Request $request */
            if ($request->getMethod() == 'POST'
                && preg_match('#^https\:\/\/api\.bitbucket\.org\/2\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/commit\/(?<sha1>[a-z0-9_-]+)\/statuses\/build$#i', (string)$request->getUri(), $matches)
            ) {
                return;
            }
        }

        throw new \RuntimeException('No build status found');
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

    /**
     * @Then the BitBucket build status of the commit :sha1 should be :state
     */
    public function theBitbucketBuildStatusOfTheCommitShouldBeInProgress($sha1, $state)
    {
        $foundStatuses = [];

        foreach ($this->guzzleHistory as $request) {
            /** @var \GuzzleHttp\Psr7\Request $request */
            if ($request->getMethod() != 'POST') {
                continue;
            } elseif (!preg_match('#^https\:\/\/api\.bitbucket\.org\/2\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/commit\/(?<sha1>[a-z0-9_-]+)\/statuses\/build$#i', (string) $request->getUri(), $matches)) {
                continue;
            } elseif ($matches['sha1'] != $sha1) {
                continue;
            }

            $body = $request->getBody();
            $body->rewind();

            $json = \GuzzleHttp\json_decode($body->getContents(), true);
            $found = strtoupper($json['state']);
            $expected = strtoupper($state);

            if ($found == $expected) {
                return true;
            }

            $foundStatuses[] = $found;
        }

        throw new \RuntimeException(sprintf(
            'Notification not found (found %s)',
            implode(', ', $foundStatuses)
        ));
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
        $addon = \GuzzleHttp\json_decode($this->readFixture('addon-installed.json'), true);
        $addon['clientKey'] = $clientKey;
        $addon['principal']['type'] = 'user';
        $addon['principal']['username'] = $principalUsername;

        return $addon;
    }

    /**
     * @param int $expectedStatus
     */
    private function assertResponseStatus(int $expectedStatus)
    {
        if ($this->response->getStatusCode() != $expectedStatus) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code %d, found %d',
                $expectedStatus,
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @param string $fixture
     *
     * @return string
     */
    private function readFixture(string $fixture): string
    {
        return file_get_contents(__DIR__ . '/../integrations/code-repositories/bitbucket/fixtures/' . $fixture);
    }

    /**
     * @param $body
     */
    private function sendWebhook($body)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/connect/service/bitbucket/addon/webhook',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($body)
        ));

        $this->assertResponseStatus(202);
    }

    /**
     * @param string $fixture
     *
     * @return array
     */
    private function webhookBoilerplate($fixture): array
    {
        $flow = $this->flowContext->getCurrentFlow();
        $repository = $flow->getCodeRepository();

        if (!$repository instanceof BitBucketCodeRepository) {
            throw new \RuntimeException('The code repository of the current flow should be a BitBucket repository');
        }

        $body = \GuzzleHttp\json_decode($this->readFixture($fixture), true);
        $body['data']['repository']['uuid'] = $repository->getIdentifier();
        $body['data']['repository']['name'] = $repository->getName();
        $body['data']['repository']['owner']['type'] = $repository->getOwner()->getType();
        $body['data']['repository']['owner']['username'] = $repository->getOwner()->getUsername();
        return $body;
    }
}
