<?php

namespace ContinuousPipe\Authenticator\Security\Authentication;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\User\WhiteList;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use ContinuousPipe\User\EmailNotFoundException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
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
     * @var WhiteList
     */
    private $whiteList;

    /**
     * @param SecurityUserRepository $securityUserRepository
     * @param UserDetails            $userDetails
     * @param WhiteList              $whiteList
     */
    public function __construct(SecurityUserRepository $securityUserRepository, UserDetails $userDetails, WhiteList $whiteList)
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->userDetails = $userDetails;
        $this->whiteList = $whiteList;
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

        if (null === $securityUser->getUser()->getEmail()) {
            try {
                $email = $this->getEmail($response);

                $securityUser->getUser()->setEmail($email);
            } catch (EmailNotFoundException $e) {
            }
        }

        /*
        $securityUser->getUser()->setGitHubCredentials(new GitHubCredentials(
            $gitHubLogin,
            $response->getAccessToken(),
            $response->getRefreshToken()
        ));
        */

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
    private function createUserFromUsername($username)
    {
        return new SecurityUser(new User($username));
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
}
