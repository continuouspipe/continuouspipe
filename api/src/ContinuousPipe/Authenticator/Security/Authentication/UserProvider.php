<?php

namespace ContinuousPipe\Authenticator\Security\Authentication;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\GitHubToken;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Authenticator\WhiteList\WhiteList;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use ContinuousPipe\Authenticator\GitHub\EmailNotFoundException;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var SecurityUserRepository
     */
    private $securityUserRepository;

    /**
     * @var UserDetails
     */
    private $userDetails;

    /**
     * @var WhiteList
     */
    private $whiteList;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param SecurityUserRepository $securityUserRepository
     * @param UserDetails            $userDetails
     * @param WhiteList              $whiteList
     * @param BucketRepository       $bucketRepository
     */
    public function __construct(SecurityUserRepository $securityUserRepository, UserDetails $userDetails, WhiteList $whiteList, BucketRepository $bucketRepository)
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->userDetails = $userDetails;
        $this->whiteList = $whiteList;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $gitHubResponse = $response->getResponse();
        $username = $gitHubResponse['login'];
        if (!$this->whiteList->contains($username)) {
            throw new InsufficientAuthenticationException(sprintf(
                'User "%s" is not in the white list, yet? :)',
                $username
            ));
        }

        try {
            $securityUser = $this->securityUserRepository->findOneByUsername($username);
        } catch (UserNotFound $e) {
            $securityUser = $this->createUserFromUsername($username);
        }

        $user = $securityUser->getUser();
        if (null === $user->getEmail()) {
            try {
                $user->setEmail($this->getEmail($response));
            } catch (EmailNotFoundException $e) {
            }
        }

        $bucket = $this->bucketRepository->find($user->getBucketUuid());
        $this->updateUserGitHubTokenInBucket($bucket, $user, $response);
        $this->bucketRepository->save($bucket);
        $this->securityUserRepository->save($securityUser);

        return $securityUser;
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return string
     */
    private function getEmail(UserResponseInterface $response)
    {
        if ($email = $response->getEmail()) {
            return $email;
        }

        return $this->userDetails->getEmailAddress($response->getAccessToken());
    }

    /**
     * @param string $username
     *
     * @return SecurityUser
     */
    public function createUserFromUsername($username)
    {
        // Create user's bucket
        $bucketUuid = Uuid::uuid1();
        $this->bucketRepository->save(new Bucket($bucketUuid));

        // Create the user
        $user = new SecurityUser(new User($username, $bucketUuid));
        $this->securityUserRepository->save($user);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->securityUserRepository->findOneByUsername($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class == SecurityUser::class;
    }

    /**
     * @param Bucket                $bucket
     * @param User                  $user
     * @param UserResponseInterface $response
     */
    private function updateUserGitHubTokenInBucket(Bucket $bucket, User $user, UserResponseInterface $response)
    {
        $tokens = $bucket->getGitHubTokens();
        $matchingTokens = $tokens->filter(function (GitHubToken $token) use ($user) {
            return $token->getLogin() == $user->getUsername();
        });

        if ($matchingTokens->count() > 0) {
            $matchingTokens->first()->setAccessToken($response->getAccessToken());
        } else {
            $tokens->add(new GitHubToken($user->getUsername(), $response->getAccessToken()));
        }
    }
}
