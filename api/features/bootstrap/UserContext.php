<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\GitHubToken;
use Rhumsaa\Uuid\Uuid;

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
     * @param SecurityUserRepository $securityUserRepository
     * @param BucketRepository $bucketRepository
     */
    public function __construct(SecurityUserRepository $securityUserRepository, BucketRepository $bucketRepository)
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->bucketRepository = $bucketRepository;
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
}
