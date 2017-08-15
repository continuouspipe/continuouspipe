<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use ContinuousPipe\Authenticator\Security\ApiKey\SystemUserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Authenticator\Security\Authentication\UserProvider;
use ContinuousPipe\Authenticator\Security\InMemoryApiKeyRepository;
use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Authenticator\Tests\Security\GitHubOAuthResponse;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Authenticator\WhiteList\WhiteList;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitHubResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

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
     * @var SystemUserApiKey
     */
    private $systemUserByApiKey;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var GitHubResourceOwner
     */
    private $gitHubResourceOwner;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var \ContinuousPipe\Security\ApiKey\UserApiKeyRepository
     */
    private $userByApiKeyRepository;

    public function __construct(
        UserProvider $userProvider,
        WhiteList $whiteList,
        TokenStorageInterface $tokenStorage,
        SecurityUserRepository $securityUserRepository,
        SystemUserApiKey $systemUserByApiKey,
        EventDispatcherInterface $eventDispatcher,
        GitHubResourceOwner $gitHubResourceOwner,
        KernelInterface $kernel,
        UserApiKeyRepository $userByApiKeyRepository
    ) {
        $this->userProvider = $userProvider;
        $this->whiteList = $whiteList;
        $this->tokenStorage = $tokenStorage;
        $this->securityUserRepository = $securityUserRepository;
        $this->systemUserByApiKey = $systemUserByApiKey;
        $this->eventDispatcher = $eventDispatcher;
        $this->gitHubResourceOwner = $gitHubResourceOwner;
        $this->kernel = $kernel;
        $this->userByApiKeyRepository = $userByApiKeyRepository;
    }

    /**
     * @Given the user :username have the API key :apiKey
     */
    public function theUserHaveTheApiKey($username, $apiKey)
    {
        $this->userByApiKeyRepository->save(new UserApiKey(
            Uuid::uuid4(),
            $this->userProvider->loadUserByUsername($username)->getUser(),
            $apiKey,
            new \DateTime()
        ));
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
     * @Given I am authenticated as admin :username
     */
    public function iAmAuthenticatedAsAdmin($username)
    {
        $token = new JWTUserToken(['ROLE_USER', 'ROLE_ADMIN']);
        $token->setUser($this->thereIsAUser($username));

        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $this->iAmAuthenticatedAsUser('Geza');
    }

    /**
     * @Given I am not authenticated
     */
    public function iAmNotAuthenticated()
    {
        $this->tokenStorage->setToken(null);
    }

    /**
     * @Given there is a user :username
     */
    public function thereIsAUser($username) : SecurityUser
    {
        try {
            return $this->securityUserRepository->findOneByUsername($username);
        } catch (UserNotFound $e) {
            return $this->userProvider->createUserFromUsername($username);
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
     * @Given there is the system api key :key
     */
    public function thereIsTheSystemApiKey($key)
    {
        $this->systemUserByApiKey->addKey($key);
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
     * @When a login with GitHub as :username with the token :token
     */
    public function aLoginWithGithubAsWithTheToken($username, $token)
    {
        try {
            $this->userProvider->loadUserByOAuthUserResponse(new GitHubOAuthResponse($username, new OAuthToken($token), null, $this->gitHubResourceOwner));
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When a user login with GitHub as :username
     */
    public function aUserLoginWithGithubAs($username)
    {
        try {
            $user = $this->userProvider->loadUserByOAuthUserResponse(new GitHubOAuthResponse($username, new OAuthToken('1234567890'), null, $this->gitHubResourceOwner));

            $token = new JWTUserToken(['ROLE_USER']);
            $token->setUser($user);

            $this->eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, new InteractiveLoginEvent(Request::create('/'), $token));
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Given the user :username with email :email is authenticated on its account
     */
    public function theUserWithEmailIsAuthenticatedOnItsAccount($username, $email)
    {
        $user = new User($username, Uuid::uuid4(), ['ROLE_USER']);
        $user->setEmail($email);

        $token = new OAuthToken('1234');
        $token->setUser(new SecurityUser($user));
        $token->setAuthenticated(true);

        $session = $this->kernel->getContainer()->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $this->tokenStorage->setToken($token);
    }

    /**
     * @When the user :username with email :email login
     */
    public function theUserWithEmailLogin($username, $email)
    {
        $roles = ['ROLE_USER'];

        $user = new User($username, Uuid::uuid4(), $roles);
        $user->setEmail($email);
        $token = new JWTUserToken($roles);
        $token->setUser(new SecurityUser($user));

        $this->userProvider->loadUserByOAuthUserResponse(new GitHubOAuthResponse($username, new OAuthToken('1234567890'), $email));

        $this->eventDispatcher->dispatch('security.interactive_login', new InteractiveLoginEvent(
            Request::create('/'),
            $token
        ));
    }

    /**
     * @Then the user :username should exists
     */
    public function theUserShouldExists($username)
    {
        $this->userProvider->loadUserByUsername($username);
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
            echo $this->exception->getMessage();
            throw new \RuntimeException('An exception was found');
        }
    }

    /**
     * @Given there is a user :username with email :email
     */
    public function thereIsAUserWithEmail($username, $email)
    {
        try {
            $user = $this->securityUserRepository->findOneByUsername($username);
        } catch (UserNotFound $e) {
            $user = $this->userProvider->createUserFromUsername($username);
        }

        $user->getUser()->setEmail($email);
        $this->securityUserRepository->save($user);

        return $user;
    }


}
