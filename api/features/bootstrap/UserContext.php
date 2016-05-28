<?php

use Behat\Behat\Context\Context;
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
     * @param SecurityUserRepository $securityUserRepository
     * @param BucketRepository $bucketRepository
     * @param KernelInterface $kernel
     */
    public function __construct(SecurityUserRepository $securityUserRepository, BucketRepository $bucketRepository, KernelInterface $kernel)
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->bucketRepository = $bucketRepository;
        $this->kernel = $kernel;
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
     * @Then I should receive the details
     */
    public function iShouldReceiveTheDetails()
    {
        if ($this->response->getStatusCode() != 200) {
            echo $this->response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected status code 200, got %d',
                $this->response->getStatusCode()
            ));
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
     * @Then I should be told that I don't have the authorization to access this user
     */
    public function iShouldBeToldThatIDonTHaveTheAuthorizationToAccessThisUser()
    {
        if ($this->response->getStatusCode() != 403) {
            throw new \RuntimeException(sprintf(
                'Expected status code 403, got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then I should be told that I am not identified
     */
    public function iShouldBeToldThatIAmNotIdentified()
    {
        if ($this->response->getStatusCode() != 401) {
            throw new \RuntimeException(sprintf(
                'Expected status code 401, got %d',
                $this->response->getStatusCode()
            ));
        }
    }
}
