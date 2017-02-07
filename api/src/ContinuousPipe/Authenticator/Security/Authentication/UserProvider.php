<?php

namespace ContinuousPipe\Authenticator\Security\Authentication;

use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\Account\GitHubAccount;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\GitHubToken;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Authenticator\WhiteList\WhiteList;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use ContinuousPipe\Authenticator\GitHub\EmailNotFoundException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AccountRepository
     */
    private $accountRepository;
    /**
     * @var AccountConnectorInterface
     */
    private $accountConnector;

    /**
     * @param SecurityUserRepository    $securityUserRepository
     * @param UserDetails               $userDetails
     * @param WhiteList                 $whiteList
     * @param BucketRepository          $bucketRepository
     * @param TeamMembershipRepository  $teamMembershipRepository
     * @param TeamRepository            $teamRepository
     * @param EventDispatcherInterface  $eventDispatcher
     * @param LoggerInterface           $logger
     * @param AccountRepository         $accountRepository
     * @param AccountConnectorInterface $accountConnector
     */
    public function __construct(SecurityUserRepository $securityUserRepository, UserDetails $userDetails, WhiteList $whiteList, BucketRepository $bucketRepository, TeamMembershipRepository $teamMembershipRepository, TeamRepository $teamRepository, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger, AccountRepository $accountRepository, AccountConnectorInterface $accountConnector)
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->userDetails = $userDetails;
        $this->whiteList = $whiteList;
        $this->bucketRepository = $bucketRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->teamRepository = $teamRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->accountRepository = $accountRepository;
        $this->accountConnector = $accountConnector;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $gitHubResponse = $response->getResponse();
        $username = $gitHubResponse['login'];
        if (!$this->whiteList->contains($username)) {
            $this->logger->warning('User is not in whitelist', [
                'username' => $username,
            ]);

            throw new InsufficientAuthenticationException(sprintf(
                'User "%s" is not in the white list, yet? :)',
                $username
            ));
        }

        try {
            $securityUser = $this->securityUserRepository->findOneByUsername($username);
            $created = false;
        } catch (UserNotFound $e) {
            $securityUser = $this->createUserFromUsername($username);
            $created = true;
        }

        $user = $this->fillUserEmail($response, $securityUser);

        // Update its GitHub token if needed
        $bucket = $this->bucketRepository->find($user->getBucketUuid());
        $this->updateUserGitHubTokenInBucket($bucket, $user, $response);
        $this->bucketRepository->save($bucket);

        // Save the user
        $this->securityUserRepository->save($securityUser);

        // Link account if not found
        if (!$this->userHasAlreadyLinkedGitHubAccount($user, $username)) {
            $this->accountConnector->connect($securityUser, $response);
        }

        // Dispatch an event is the user was just created
        if ($created) {
            $this->eventDispatcher->dispatch(UserCreated::EVENT_NAME, new UserCreated($user));
        }

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
        $securityUser = new SecurityUser(new User($username, $bucketUuid));
        $this->securityUserRepository->save($securityUser);

        return $securityUser;
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
            return $token->getIdentifier() == $user->getUsername();
        });

        if ($matchingTokens->count() > 0) {
            $matchingTokens->first()->setAccessToken($response->getAccessToken());
        } else {
            $tokens->add(new GitHubToken($user->getUsername(), $response->getAccessToken()));
        }
    }

    /**
     * @param UserResponseInterface $response
     * @param SecurityUser          $securityUser
     *
     * @return User
     */
    private function fillUserEmail(UserResponseInterface $response, SecurityUser $securityUser)
    {
        $user = $securityUser->getUser();

        if (null === $user->getEmail()) {
            try {
                $user->setEmail($this->getEmail($response));
            } catch (EmailNotFoundException $e) {
            }
        }

        return $user;
    }

    /**
     * @param User   $user
     * @param string $username
     *
     * @return bool
     */
    private function userHasAlreadyLinkedGitHubAccount(User $user, string $username)
    {
        $accounts = $this->accountRepository->findByUsername($user->getUsername());

        foreach ($accounts as $account) {
            if (!$account instanceof GitHubAccount) {
                continue;
            }

            if ($account->getUsername() == $username) {
                return true;
            }
        }

        return false;
    }
}
