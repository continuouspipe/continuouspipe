<?php

namespace Pipe;

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;

class ApiContext implements Context
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var InMemoryAuthenticatorClient
     */
    private $authenticatorClient;
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param InMemoryAuthenticatorClient $authenticatorClient
     * @param KernelInterface $kernel
     */
    public function __construct(TokenStorageInterface $tokenStorage, InMemoryAuthenticatorClient $authenticatorClient, KernelInterface $kernel)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticatorClient = $authenticatorClient;
        $this->kernel = $kernel;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $user = new User('samuel', Uuid::uuid1());
        $this->authenticatorClient->addUser($user);

        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new SecurityUser($user));

        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given I am not authenticated
     */
    public function iAmNotAuthenticated()
    {
        $this->tokenStorage->setToken(null);
    }

    /**
     * @When I request the page at :path
     */
    public function iRequestThePageAt($path)
    {
        $this->response = $this->kernel->handle(Request::create($path));
    }

    /**
     * @Then the response status code should be :statusCode
     */
    public function theResponseStatusCodeShouldBe($statusCode)
    {
        if ($this->response->getStatusCode() != $statusCode) {
            throw new \RuntimeException(sprintf(
                'Expected %d but got %d',
                $statusCode,
                $this->response->getStatusCode()
            ));
        }
    }
}
