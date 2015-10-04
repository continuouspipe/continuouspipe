<?php

namespace ContinuousPipe\Authenticator\Security\Authentication;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use ContinuousPipe\User\EmailNotFoundException;
use ContinuousPipe\User\GitHubCredentials;
use ContinuousPipe\User\SecurityUser;
use ContinuousPipe\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
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
     * @param SecurityUserRepository $securityUserRepository
     * @param UserDetails            $userDetails
     */
    public function __construct(SecurityUserRepository $securityUserRepository, UserDetails $userDetails)
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->userDetails = $userDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $gitHubResponse = $response->getResponse();
        $gitHubLogin = $gitHubResponse['login'];

        $email = $this->getEmail($response);

        try {
            $securityUser = $this->securityUserRepository->findOneByEmail($email);
        } catch (UserNotFound $e) {
            $securityUser = $this->createUserFromEmail($email);
        }

        $securityUser->getUser()->setGitHubCredentials(new GitHubCredentials(
            $gitHubLogin,
            $response->getAccessToken(),
            $response->getRefreshToken()
        ));

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

        try {
            return $this->userDetails->getEmailAddress($response->getAccessToken());
        } catch (EmailNotFoundException $e) {
            throw new UnsupportedUserException('User must have an email');
        }
    }

    /**
     * @param string $email
     *
     * @return SecurityUser
     */
    private function createUserFromEmail($email)
    {
        return new SecurityUser(new User($email));
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->securityUserRepository->findOneByEmail($username);
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
}
