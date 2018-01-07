<?php

namespace River;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\AtlassianAddon\Account;
use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\TraceableInstallationRepository;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketAccount;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClient;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientException;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientFactory;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\Guzzle\MatchingHandler;
use ContinuousPipe\River\Tests\CodeRepository\GitHub\FakePullRequestResolver;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use Helpers\FixturesHelper;
use Lcobucci\JWT\Builder;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerInterface;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class BitBucketContext implements CodeRepositoryContext
{
    use FixturesHelper;

    /**
     * @var FlowContext
     */
    private $flowContext;
    /**
     * @var TideContext
     */
    private $tideContext;
    /**
     * @var \River\BitBucket\AddonContext
     */
    private $addonContext;
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

    /**
     * @var array
     */
    private $commitBuilder = [];
    /**
     * @var CodeRepository\InMemoryBranchQuery
     */
    private $inMemoryBranchQuery;
    /**
     * @var FakePullRequestResolver
     */
    private $fakePullRequestResolver;
    private $repository;

    public function __construct(
        MatchingHandler $bitBucketMatchingClientHandler,
        KernelInterface $kernel,
        TraceableInstallationRepository $traceableInstallationRepository,
        SerializerInterface $serializer,
        BitBucketClientFactory $clientFactory,
        History $guzzleHistory,
        InMemoryCodeRepositoryRepository $inMemoryCodeRepositoryRepository,
        CodeRepository\InMemoryBranchQuery $inMemoryBranchQuery,
        FakePullRequestResolver $fakePullRequestResolver
    ) {
        $this->bitBucketMatchingClientHandler = $bitBucketMatchingClientHandler;
        $this->kernel = $kernel;
        $this->traceableInstallationRepository = $traceableInstallationRepository;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
        $this->guzzleHistory = $guzzleHistory;
        $this->inMemoryCodeRepositoryRepository = $inMemoryCodeRepositoryRepository;
        $this->inMemoryBranchQuery = $inMemoryBranchQuery;
        $this->fakePullRequestResolver = $fakePullRequestResolver;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->flowContext = $scope->getEnvironment()->getContext('River\FlowContext');
        $this->tideContext = $scope->getEnvironment()->getContext('River\TideContext');
        $this->addonContext = $scope->getEnvironment()->getContext('River\BitBucket\AddonContext');
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
        $this->repository = $repository;

        $this->addonContext->thereIsTheAddOnInstalledForTheBitbucketRepositoryOwnedByUser($name, $username);

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
     * @Given I have a :filePath file in my repository that contains:
     */
    public function iHaveAFileInMyRepositoryThatContains($filePath, PyStringNode $string)
    {
        $this->thereIsAFileContaining($filePath, $string->getRaw());
    }

    public function thereIsAFileContaining(string $filePath, string $contents)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($filePath) {
                return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.bitbucket\.org\/1\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/src\/([a-z0-9_-]+)\/'.$filePath.'$#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => $contents,
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
     * @Given the pull-request #:identifier do not contains the tide-related commit
     */
    public function thePullRequestDoNotContainsTheTideRelatedCommit($identifier)
    {
        $pullRequests = \GuzzleHttp\json_decode($this->readFixture('list-of-opened-pull-requests.json'), true);
        $pullRequests['values'][0]['id'] = (int) $identifier;
        $pullRequests['values'][0]['source']['commit']['hash'] = md5(Uuid::uuid4()->toString());
        $pullRequests['values'][0]['source']['branch']['name'] = Uuid::uuid4()->toString();

        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) {
                return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.bitbucket\.org\/2\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/pullrequests\?state\=OPEN$#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode($pullRequests)),
        ]);
    }

    /**
     * @Given the BitBucket URL :url will return :response with the header :headerName
     */
    public function theBitbucketUrlWillReturnWithTheHeader($url, $response, $headerName)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($url, $headerName) {
                return $request->getUri() == $url && !empty($request->getHeaderLine($headerName));
            },
            'response' => new \GuzzleHttp\Psr7\Response(200, [], $response),
        ]);
    }

    /**
     * @Given the BitBucket URL :url will return the content of the fixtures file :fileName with the header :headerName
     */
    public function theBitbucketUrlWillReturnTheContentOfTheFixturesFileWithTheHeader($url, $fileName, $headerName)
    {
        $this->theBitbucketUrlWillReturnWithTheHeader($url, file_get_contents(__DIR__.'/../../river/fixtures/'.$fileName), $headerName);
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
     * @Then the addresses of the environment should not be commented on the pull-request
     */
    public function theAddressesOfTheEnvironmentShouldNotBeCommentedOnThePullRequest()
    {
        foreach ($this->guzzleHistory as $request) {
            /** @var \GuzzleHttp\Psr7\Request $request */
            if ($request->getMethod() == 'POST' &&
                preg_match('#^https\:\/\/api\.bitbucket\.org\/1\.0\/repositories\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/pullrequests\/([a-z0-9_-]+)\/comments$#i', (string) $request->getUri())) {
                throw new \RuntimeException('A comment have been created');
            }
        }
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
     * @When a pull-request is created with good signature
     */
    public function aPullRequestIsCreatedWithGoodSignature()
    {
        try {
            $body = $this->webhookBoilerplate('webhook/pull-request-created.json');
            $this->sendWebhook($body);
        } catch (\RuntimeException $e) {
        }
    }

    /**
     * @When a pull-request is created with invalid signature
     */
    public function aPullRequestIsCreatedWithInvalidSignature()
    {
        try {
            $webTokenOptions = ['valid_signature' => false];
            $body = $this->webhookBoilerplate('webhook/pull-request-created.json');
            $this->sendWebhook($body, $webTokenOptions);
        } catch (\RuntimeException $e) {
        }
    }

    /**
     * @When a pull-request is created without JSON Web Token
     */
    public function aPullRequestIsCreatedWithoutJsonWebToken()
    {
        try {
            $webTokenOptions = ['enabled' => false];
            $body = $this->webhookBoilerplate('webhook/pull-request-created.json');
            $this->sendWebhook($body, $webTokenOptions);
        } catch (\RuntimeException $e) {
        }
    }

    /**
     * @When a pull-request is created with good signature using the :algorithm algorithm
     */
    public function aPullRequestIsCreatedWithGoodSignatureAndAlgorithm()
    {
        try {
            $webTokenOptions = ['algorithm' => new \BitBucket\Integration\Signer\None()];
            $body = $this->webhookBoilerplate('webhook/pull-request-created.json');
            $this->sendWebhook($body, $webTokenOptions);
        } catch (\RuntimeException $e) {
        }
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
     * @Given the BitBucket user :username have the following repositories on the page :page of :pageCount:
     */
    public function theBitbucketUserHaveTheFollowingRepositoriesOnThePageOf($username, TableNode $table, $page = 1, $pageCount = 1)
    {
        $this->bitBucketMatchingClientHandler->shiftMatcher([
            'match' => function(RequestInterface $request) use ($username, $page) {
                $uri = $request->getUri() == 'https://api.bitbucket.org/2.0/repositories/'.$username;

                if ($page != 1) {
                    $uri .= '?page='.$page;
                }

                return $request->getMethod() == 'GET' && $uri;
            },
            'response' => $this->createRepositoriesResponse($username, $table, $page, $pageCount, 'https://api.bitbucket.org/2.0/repositories/'.$username),
        ]);
    }

    /**
     * @Given the BitBucket team :team have the following repositories:
     * @Given the BitBucket team :team have the following repositories on the page :page of :pageCount:
     */
    public function theBitbucketTeamHaveTheFollowingRepositories($team, TableNode $table, $page = 1, $pageCount = 1)
    {
        $this->bitBucketMatchingClientHandler->shiftMatcher([
            'match' => function(RequestInterface $request) use ($team, $page) {
                $uri = 'https://api.bitbucket.org/2.0/teams/'.$team.'/repositories';
                if ($page != 1) {
                    $uri .= '?page='.$page;
                }

                return $request->getMethod() == 'GET' && $request->getUri() == $uri;
            },
            'response' => $this->createRepositoriesResponse($team, $table, $page, $pageCount, 'https://api.bitbucket.org/2.0/teams/'.$team.'/repositories'),
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
     * @When I push the anonymous commit :sha1 to the branch :branch of the BitBucket repository :repositoryName owned by :ownerType :ownerUsername
     */
    public function iPushTheAnonymousCommitToTheBranchOfTheBitbucketRepositoryOwnedByUser($sha1, $branch, $repositoryName, $ownerType, $ownerUsername)
    {
        $body = \GuzzleHttp\json_decode($this->readFixture('webhook/pushed-in-branch.json'), true);
        $body['data']['repository']['uuid'] = Uuid::uuid5(Uuid::NIL, $repositoryName)->toString();
        $body['data']['repository']['name'] = $repositoryName;
        $body['data']['repository']['owner']['type'] = strtolower($ownerType);
        $body['data']['repository']['owner']['username'] = $ownerUsername;
        $body['data']['push']['changes'][0]['new']['type'] = 'branch';
        $body['data']['push']['changes'][0]['new']['name'] = $branch;
        $body['data']['push']['changes'][0]['new']['target']['hash'] = $sha1;
        $body['data']['push']['changes'][0]['new']['target']['author']['user'] = null;
        $body['data']['push']['changes'][0]['commits'] = array_map(function(array $commit) {
            unset($commit['author']['user']);

            return $commit;
        }, $body['data']['push']['changes'][0]['commits']);

        $this->sendWebhook($body);
    }

    /**
     * @When the commit :sha is pushed to the branch :branch by the user :username with an email :email
     */
    public function theCommitIsPushedToTheBranchByTheUserWithAnEmail($sha, $branch, $username, $email)
    {
        $body = $this->webhookBoilerplate('webhook/pushed-in-branch.json');
        $body['data']['push']['changes'][0]['new']['type'] = 'branch';
        $body['data']['push']['changes'][0]['new']['name'] = $branch;
        $body['data']['push']['changes'][0]['new']['target']['hash'] = $sha;

        $commitTemplate = $body['data']['push']['changes'][0]['commits'][0];
        $commitTemplate['author']['raw'] = $username.' <'.$email.'>';
        $commitTemplate['author']['user']['username'] = $username;
        $commitTemplate['author']['user']['display_name'] = $username;

        $body['data']['push']['changes'][0]['commits'] = [$commitTemplate];

        $this->sendWebhook($body);
    }

    /**
     * @Given the commit :sha has been written by the user :username with an email :email
     */
    public function theCommitHasBeenWrittenByTheUserWithAnEmail($sha, $username, $email)
    {
        $this->commitBuilder[$sha] = ['username' => $username, 'email' => $email];
    }

    /**
     * @When the commits :commits are pushed to the branch :branch
     */
    public function theCommitsArePushedToTheBranch($commits, $branch)
    {
        $sha1s = explode(',', $commits);
        $body = $this->webhookBoilerplate('webhook/pushed-in-branch.json');
        $body['data']['push']['changes'][0]['new']['type'] = 'branch';
        $body['data']['push']['changes'][0]['new']['name'] = $branch;
        $body['data']['push']['changes'][0]['new']['target']['hash'] = end($sha1s);

        $commitTemplate = $body['data']['push']['changes'][0]['commits'][0];
        $body['data']['push']['changes'][0]['commits'] = [];

        foreach ($sha1s as $sha) {
            $commitDetails = $this->commitBuilder[$sha];

            $commit = $commitTemplate;
            $commit['hash'] = $sha;
            $commit['author']['raw'] = $commitDetails['username'] . ' <' . $commitDetails['email'] . '>';
            $commit['author']['user']['username'] = $commitDetails['username'];
            $commit['author']['user']['display_name'] = $commitDetails['username'];

            $body['data']['push']['changes'][0]['commits'][] = $commit;
        }

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
     * @Given there is a :path file in my BitBucket repository that contains:
     * @Given there is a :path file in the BitBucket repository :slug owned by :owner that contains:
     */
    public function thereIsAFileInMyBitbucketRepositoryThatContains($path, PyStringNode $string, $slug = null, $owner = null)
    {
        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($path, $slug, $owner) {
                if ($request->getMethod() != 'GET') {
                    return false;
                }

                if (!preg_match('#^https\:\/\/api\.bitbucket\.org\/1\.0\/repositories\/'.($owner ?: '([a-z0-9_-]+)').'\/'.($slug ?: '([a-z0-9_-]+)').'\/src\/([a-z0-9_-]+)\/(?<path>.+)$#i', (string) $request->getUri(), $matches)) {
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

        $this->client->getReference(
            new BitBucketCodeRepository(
                '{'.Uuid::uuid4()->toString().'}',
                new BitBucketAccount('UUID', 'owner', 'user', 'owner display name'),
                'repository',
                'https://api.bitbucket.org/2.0/repositories/owner/repository',
                'develop',
                'private'
            ),
            'branch'
        );
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

    /**
     * @Then processing the webhook should be accepted
     */
    public function processingTheWebhookShouldBeSuccessfullyCompleted()
    {
        $this->assertResponseStatus(202);
    }

    /**
     * @Then processing the webhook should be denied
     */
    public function processingTheWebhookShouldBeDenied()
    {
        $this->assertResponseStatus(403);
    }

    /**
     * @Given the following branches exist in the bitbucket repository with slug :slug for user :user:
     */
    public function theFollowingBranchesExistInTheBitbucketRepositoryWithSlugForUser(TableNode $table, $slug, $user)
    {
        $url = "https://api.bitbucket.org/2.0/repositories/$user/$slug/refs/branches";

        $branches = array_map(function(array $b) {
            $branch =  [
                'name' => $b['name'],
            ];
            if (isset($b['sha']) && isset($b['commit-url'])) {
                $branch['target'] = [
                    'hash' => $b['sha'],
                    'links' => ['html' => ['href' => $b['commit-url']]],
                ];
            }

            if (isset($b['datetime'])) {
                $branch['target']['date'] = $b['datetime'];
            }

            return $branch;
        }, $table->getHash());

        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($url) {
                return $request->getMethod() == 'GET' && ((string) $request->getUri() == $url);
            },
            'response' => new \GuzzleHttp\Psr7\Response(200, [], \GuzzleHttp\json_encode(['values' => $branches])),
        ]);
        $this->inMemoryBranchQuery->notOnlyInMemory();
    }

    /**
     * @Given the following branches exist in the bitbucket repository with slug :slug and are paginated in the api response:
     */
    public function theFollowingBranchesExistsInTheBitbucketRepositoryAndArePaginatedInTheApiResponse(TableNode $table, $slug)
    {
        $url = 'https://api.bitbucket.org/2.0/repositories/sroze/' . $slug . '/refs/branches';

        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($url) {
                return $request->getMethod() == 'GET' && ((string) $request->getUri() == $url);
            },
            'response' => new \GuzzleHttp\Psr7\Response(200, [], \GuzzleHttp\json_encode(
                [
                    'values' => [$table->getHash()[0]],
                    'next' => $url . '?page=2'
                ]
            )),
        ]);

        $this->bitBucketMatchingClientHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($url) {
                return $request->getMethod() == 'GET' && ((string) $request->getUri() == $url . '?page=2');
            },
            'response' => new \GuzzleHttp\Psr7\Response(200, [], \GuzzleHttp\json_encode(
                [
                    'values' => array_slice($table->getHash(), 1)
                ]
            )),
        ]);
        $this->inMemoryBranchQuery->notOnlyInMemory();
    }

    /**
     * @Given there is a Bitbucket pull-request #:number titled :title for branch :branch
     */
    public function aPullRequestContainsTheTideRelatedCommit($number, $title, $branch)
    {
        $this->fakePullRequestResolver->willResolve([
            CodeRepository\PullRequest::bitbucket($number, $this->repository->getAddress(), $title, isset($branch) ? new Branch($branch): null),
        ]);
    }

    private function createRepositoriesResponse(string $username, TableNode $table, int $currentPage, int $pageCount, string $pageUrl): Response
    {
        $body = [
            'pagelen' => 10,
            'page' => $currentPage,
            'size' => 10 * $pageCount,
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
        ];

        if ($pageCount > 1 && $currentPage < $pageCount) {
            $body['next'] = $pageUrl.'?page='.($currentPage + 1);
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($body));
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
     * @param $body
     * @param array $webTokenOptions
     */
    private function sendWebhook($body, $webTokenOptions = [])
    {
        $defaultWebTokenOptions = [
            'enabled' => true,
            'valid_signature' => true,
            'algorithm' => new Sha256(),
        ];
        $webTokenOptions = array_merge($defaultWebTokenOptions, $webTokenOptions);

        $request = Request::create(
            '/connect/service/bitbucket/addon/webhook',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($body)
        );
        if ($webTokenOptions['enabled']) {
            $this->addJsonWebTokenToRequest($body, $webTokenOptions, $request);
        }
        $this->response = $this->kernel->handle($request);

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

    private function createJsonWebToken(Account $account, array $webTokenOptions): \Lcobucci\JWT\Token
    {
        $jwtBuilder = new Builder();
        $signer = $webTokenOptions['algorithm'];
        $installationRepository = $this->traceableInstallationRepository;
        $installation = current($installationRepository->findByPrincipal($account->getType(), $account->getUsername()));

        if (!$installation instanceof Installation) {
            $currentType = gettype($installation) === 'object' ? get_class($installation) : gettype($installation);
            throw new \UnexpectedValueException(
                sprintf('Expected to get type %s, but got "%s".', Installation::class, $currentType)
            );
        }

        $secretKey = $webTokenOptions['valid_signature']
            ? $installation->getSharedSecret()
            : $installation->getSharedSecret() . '_invalid';
        return $jwtBuilder
            ->setIssuer('com.atlassian.bitbucket')
            ->setIssuedAt(time())
            ->setExpiration(time() + 3600)
            ->set('qsh', '88396352255a6c933def07620a3281c9e27d8c668e0f4d01a8ecdbb74ca52c97')
            ->setSubject($installation->getClientKey())
            ->sign($signer, $secretKey)
            ->getToken();
    }

    private function addJsonWebTokenToRequest(array $body, array $webTokenOptions, Request $request)
    {
        $accountData = json_encode(
            [
                'uuid'     => $body['data']['repository']['owner']['uuid'],
                'username' => $body['data']['repository']['owner']['username'],
                'type'     => $body['data']['repository']['owner']['type'],
            ]
        );
        $account = $this->serializer->deserialize($accountData, Account::class, 'json');
        $jwt = $this->createJsonWebToken($account, $webTokenOptions);
        $request->headers->set('Authorization', 'JWT ' . $jwt);
    }

    private function readFixture($fixture)
    {
        return $this->loadFixture($fixture, 'river/integrations/code-repositories/bitbucket');
    }
}
