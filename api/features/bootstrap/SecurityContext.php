<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use ContinuousPipe\Authenticator\Security\Authentication\UserProvider;
use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Authenticator\Tests\Security\GitHubOAuthResponse;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Authenticator\WhiteList\WhiteList;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityContext implements Context, SnippetAcceptingContext
{
    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * @var WhiteList
     */
    private $whiteList;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var \Exception|null
     */
    private $exception = null;
    /**
     * @var SecurityUserRepository
     */
    private $securityUserRepository;

    /**
     * @param UserProvider $userProvider
     * @param WhiteList $whiteList
     * @param TokenStorageInterface $tokenStorage
     * @param SecurityUserRepository $securityUserRepository
     */
    public function __construct(UserProvider $userProvider, WhiteList $whiteList, TokenStorageInterface $tokenStorage, SecurityUserRepository $securityUserRepository)
    {
        $this->userProvider = $userProvider;
        $this->whiteList = $whiteList;
        $this->tokenStorage = $tokenStorage;
        $this->securityUserRepository = $securityUserRepository;
    }

    /**
     * @Given I am authenticated as user :username
     */
    public function iAmAuthenticatedAsUser($username)
    {
        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser($this->thereIsAUser($username));

        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given there is a user :username
     */
    public function thereIsAUser($username)
    {
        try {
            return $this->securityUserRepository->findOneByUsername($username);
        } catch (UserNotFound $e) {
            return $this->securityUserRepository->save(new SecurityUser(
                new User($username)
            ));
        }
    }

    /**
     * @Given The user :username is not in the white list
     */
    public function theUserIsNotInTheWhiteList($username)
    {
        $this->whiteList->remove($username);
    }

    /**
     * @Given The user :username is in the white list
     */
    public function theUserIsInTheWhiteList($username)
    {
        $this->whiteList->add($username);
    }

    /**
     * @When the user :username try to authenticate himself with GitHub
     */
    public function theUserTryToAuthenticateHimselfWithGithub($username)
    {
        try {
            $this->userProvider->loadUserByOAuthUserResponse(new GitHubOAuthResponse($username));
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then the authentication should be failed
     */
    public function theAuthenticationShouldBeFailed()
    {
        if (null === $this->exception) {
            throw new \RuntimeException('No authentication exception found');
        }
    }

    /**
     * @Then the authentication should be successful
     */
    public function theAuthenticationShouldBeSuccessful()
    {
        if (null !== $this->exception) {
            throw new \RuntimeException('An exception was found');
        }
    }
}
