<?php

namespace AppBundle\Security\Authentication;

use AppBundle\Security\User\SecurityUserRepository;
use AppBundle\Security\User\UserNotFound;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Kubernetes\Manager\User\GitHubCredentials;
use Kubernetes\Manager\User\SecurityUser;
use Kubernetes\Manager\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var SecurityUserRepository
     */
    private $securityUserRepository;

    public function __construct(SecurityUserRepository $securityUserRepository)
    {
        $this->securityUserRepository = $securityUserRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $email = $response->getEmail();
        if (empty($email)) {
            throw new UnsupportedUserException('User must have an email');
        }

        try {
            $securityUser = $this->securityUserRepository->findOneByEmail($email);
        } catch (UserNotFound $e) {
            $securityUser = $this->createUserFromOAuthUserResponse($response);
        }

        $securityUser->getUser()->setGitHubCredentials(new GitHubCredentials(
            $response->getAccessToken(),
            $response->getRefreshToken()
        ));

        $this->securityUserRepository->save($securityUser);

        return $securityUser;
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return SecurityUser
     */
    private function createUserFromOAuthUserResponse(UserResponseInterface $response)
    {
        $user = new User($response->getEmail());
        $securityUser = new SecurityUser($user);

        return $securityUser;
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
