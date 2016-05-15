<?php

namespace ContinuousPipe\Authenticator\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationProvider
{
    const QUERY_CALLBACK_KEY = 'callback';
    const COOKIE_CALLBACK_KEY = 'callback';

    /**
     * @var JWTManagerInterface
     */
    protected $jwtManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $defaultRedirectionUrl;

    /**
     * @param RouterInterface       $router
     * @param JWTManagerInterface   $jwtManager
     * @param TokenStorageInterface $tokenStorage
     * @param string                $defaultRedirectionUrl
     */
    public function __construct(RouterInterface $router, JWTManagerInterface $jwtManager, TokenStorageInterface $tokenStorage, $defaultRedirectionUrl)
    {
        $this->router = $router;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->defaultRedirectionUrl = $defaultRedirectionUrl;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getAuthenticationResponse(Request $request)
    {
        if (null === ($callback = $request->query->get(self::QUERY_CALLBACK_KEY))) {
            $callback = $this->defaultRedirectionUrl;
        }

        $response = new RedirectResponse($this->router->generate('hwi_oauth_connect'));
        $response->headers->setCookie(new Cookie(self::COOKIE_CALLBACK_KEY, $callback));

        return $response;
    }

    public function getSuccessfullyAuthenticatedResponse(Request $request)
    {
        $securityToken = $this->tokenStorage->getToken();
        $user = $securityToken->getUser();
        $jwtToken = $this->jwtManager->create($user);

        if (null === ($callback = $request->cookies->get(self::COOKIE_CALLBACK_KEY))) {
            $callback = $this->defaultRedirectionUrl;
        }

        $url = $callback.'?token='.$jwtToken;

        return new RedirectResponse($url);
    }
}
