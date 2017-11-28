<?php

namespace Authenticator;

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\GitHubToken;
use ContinuousPipe\Security\User\UserRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class UserContext implements Context
{
    /**
     * @var SecurityUserRepository
     */
    private $securityUserRepository;
    /**
     * @var BucketRepository
     */
    private $bucketRepository;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var Response|null
     */
    private $response;
    /**
     * @var UserApiKeyRepository
     */
    private $userByApiKeyRepository;

    public function __construct(
        SecurityUserRepository $securityUserRepository,
        BucketRepository $bucketRepository,
        KernelInterface $kernel,
        UserApiKeyRepository $userByApiKeyRepository
    ) {
        $this->securityUserRepository = $securityUserRepository;
        $this->bucketRepository = $bucketRepository;
        $this->kernel = $kernel;
        $this->userByApiKeyRepository = $userByApiKeyRepository;
    }

    /**
     * @Given the bucket of the user :username is the :bucket
     */
    public function theBucketOfTheUserIsThe($username, $bucket)
    {
        $securityUser = $this->securityUserRepository->findOneByUsername($username);

        $bucketUuid = Uuid::fromString($bucket);
        $this->bucketRepository->save(new Bucket($bucketUuid));

        $user = $securityUser->getUser();
        $user->setBucketUuid($bucketUuid);

        $this->securityUserRepository->save($securityUser);
    }

    /**
     * @Given the user :username have the role :role
     */
    public function theUserHaveTheRole($username, $role)
    {
        $user = $this->securityUserRepository->findOneByUsername($username);
        $user->getUser()->setRoles(array_merge($user->getRoles(), [$role]));

        $this->securityUserRepository->save($user);
    }

    /**
     * @Given the email of the user :username is :email
     */
    public function theEmailOfTheUserIs($username, $email)
    {
        $user = $this->securityUserRepository->findOneByUsername($username);
        $user->getUser()->setEmail($email);

        $this->securityUserRepository->save($user);
    }

    /**
     * @When I request the details of user :username
     */
    public function iRequestTheDetailsOfUser($username)
    {
        $this->response = $this->kernel->handle(Request::create(sprintf('/api/user/%s', $username), 'GET'));
    }

    /**
     * @When I request the details of user :username with the api key :key
     */
    public function iRequestTheDetailsOfUserWithTheApiKey($username, $key)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/user/%s', $username),
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $key
            ]
        ));
    }

    /**
     * @When I request the user behind the API key :key with the API key :systemKey
     */
    public function iRequestTheUserBehindTheApiKey($key, $systemKey)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/api-keys/%s/user', $key),
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $systemKey
            ]
        ));
    }

    /**
     * @When I create an API key described :description for the user :username
     */
    public function iCreateAnApiKeyDescribedForTheUser($description, $username)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/user/%s/api-keys', $username),
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode([
                'description' => $description,
            ])
        ));
    }

    /**
     * @When I request the list of API keys of the user :username
     */
    public function iRequestTheListOfApiKeysOfTheUser($username)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/user/%s/api-keys', $username),
            'GET'
        ));
    }

    /**
     * @When I delete the API key :key of the user :username
     */
    public function iDeleteTheApiKeyOfTheUser($key, $username)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/user/%s/api-keys/%s', $username, $key),
            'DELETE'
        ));

        $this->assertResponseCode(204);
    }

    /**
     * @Then the bucket of the user :username should contain the GitHub token :token
     */
    public function theBucketOfTheUserShouldContainTheGithubToken($username, $token)
    {
        $user = $this->securityUserRepository->findOneByUsername($username)->getUser();
        $bucket = $this->bucketRepository->find($user->getBucketUuid());
        $matchingTokens = $bucket->getGitHubTokens()->filter(function(GitHubToken $found) use ($token) {
            return $found->getAccessToken() == $token;
        });

        if (0 == count($matchingTokens)) {
            throw new \RuntimeException('No matching token found');
        }
    }

    /**
     * @Then I should see that the user have the role :role
     */
    public function iShouldSeeThatTheUserHaveTheRole($role)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if (!in_array($role, $json['roles'])) {
            echo $this->response->getContent();

            throw new \RuntimeException('Role not found');
        }
    }

    /**
     * @Then I should see the API key :apiKey
     */
    public function iShouldSeeTheApiKey($apiKey)
    {
        $this->assertResponseCode(200);

        $keys = \GuzzleHttp\json_decode($this->response->getContent(), true);
        foreach ($keys as $key) {
            if ($key['api_key'] == $apiKey) {
                return;
            }
        }

        throw new \RuntimeException('API key not found');
    }

    /**
     * @Then I should not see the API key :apiKey
     */
    public function iShouldNotSeeTheApiKey($apiKey)
    {
        $this->assertResponseCode(200);

        $keys = \GuzzleHttp\json_decode($this->response->getContent(), true);
        foreach ($keys as $key) {
            if ($key['api_key'] == $apiKey) {
                throw new \RuntimeException('API key found');
            }
        }
    }

    /**
     * @Then I should see the user :username for this API key
     */
    public function iShouldSeeTheUserForThisApiKey($username)
    {
        $this->assertResponseCode(200);
        $response = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if ($response['username'] != $username) {
            throw new \RuntimeException(sprintf(
                'Found "%s" instead',
                $response['username']
            ));
        }
    }

    /**
     * @Then the API key should have been created
     */
    public function theApiKeyShouldHaveBeenCreated()
    {
        $this->assertResponseCode(201);
    }

    /**
     * @Then I should receive the details
     */
    public function iShouldReceiveTheDetails()
    {
        $this->assertResponseCode(200);
    }

    /**
     * @Then I should be told that the API key is not found
     */
    public function iShouldBeToldThatTheApiKeyIsNotFound()
    {
        $this->assertResponseCode(404);
    }

    /**
     * @Then I should be told that I don't have the authorization to access this user
     * @Then I should be told that I don't have the authorization to access this API key
     */
    public function iShouldBeToldThatIDonTHaveTheAuthorizationToAccessThisUser()
    {
        $this->assertResponseCode(403);
    }

    /**
     * @Then I should be told that I am not identified
     */
    public function iShouldBeToldThatIAmNotIdentified()
    {
        $this->assertResponseCode(401);
    }

    private function assertResponseCode(int $code)
    {
        if ($this->response->getStatusCode() != $code) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code %d, got %d',
                $code,
                $this->response->getStatusCode()
            ));
        }
    }
}
