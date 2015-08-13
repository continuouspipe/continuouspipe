<?php

namespace ContinuousPipe\Authenticator\Security\Authentication;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use ContinuousPipe\User\GitHubCredentials;
use ContinuousPipe\User\SecurityUser;
use ContinuousPipe\User\User;
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
        $email = $response->getEmail();
        if (empty($email)) {
            $email = $this->userDetails->getEmailAddress($response->getAccessToken());
        }

        try {
            $securityUser = $this->securityUserRepository->findOneByEmail($email);
        } catch (UserNotFound $e) {
            $securityUser = $this->createUserFromEmail($email);
        }

        $gitHubResponse = $response->getResponse();
        $securityUser->getUser()->setGitHubCredentials(new GitHubCredentials(
            $gitHubResponse['login'],
            $response->getAccessToken(),
            $response->getRefreshToken()
        ));

        $this->securityUserRepository->save($securityUser);

        return $securityUser;
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
