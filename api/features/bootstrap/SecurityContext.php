<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use ContinuousPipe\Authenticator\Security\Authentication\UserProvider;
use ContinuousPipe\Authenticator\Tests\InMemoryWhiteList;
use ContinuousPipe\Authenticator\Tests\Security\GitHubOAuthResponse;

class SecurityContext implements Context, SnippetAcceptingContext
{
    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * @var InMemoryWhiteList
     */
    private $whiteList;

    /**
     * @var \Exception|null
     */
    private $exception = null;

    /**
     * @param UserProvider $userProvider
     * @param InMemoryWhiteList $whiteList
     */
    public function __construct(UserProvider $userProvider, InMemoryWhiteList $whiteList)
    {
        $this->userProvider = $userProvider;
        $this->whiteList = $whiteList;
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
