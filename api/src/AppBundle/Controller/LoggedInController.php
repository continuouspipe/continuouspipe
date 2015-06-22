<?php

namespace AppBundle\Controller;

use AppBundle\Security\AuthenticationProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route(service="app.controller.logged_in")
 */
class LoggedInController
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
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        return $this->authenticationProvider->getSuccessfullyAuthenticatedResponse($request);
    }
}
