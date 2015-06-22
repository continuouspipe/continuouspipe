<?php

namespace AppBundle\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function __construct(RouterInterface $router, JWTManagerInterface $jwtManager, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getAuthenticationResponse(Request $request)
    {
        if (null === ($callback = $request->query->get(self::QUERY_CALLBACK_KEY))) {
            return new Response('No callback provided', Response::HTTP_BAD_REQUEST);
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
            return new Response('No callback found', Response::HTTP_BAD_REQUEST);
        }

        $url = $callback.'?token='.$jwtToken;

        return new RedirectResponse($url);
    }
}
