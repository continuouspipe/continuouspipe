<?php

namespace AppBundle\Controller;

use ContinuousPipe\Authenticator\Security\AuthenticationProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="app.controller.authenticate")
 */
class AuthenticateController
{
    /**
     * @var AuthenticationProvider
     */
    private $authenticationProvider;

    /**
     * @param AuthenticationProvider $authenticationProvider
     */
    public function __construct(AuthenticationProvider $authenticationProvider)
    {
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * @Route("/authenticate")
     */
    public function authenticate(Request $request)
    {
        return $this->authenticationProvider->getAuthenticationResponse($request);
    }
}
