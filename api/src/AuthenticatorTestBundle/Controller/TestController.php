<?php

namespace AuthenticatorTestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route(path="/test", service="app.controllers.test_controller")
 */
class TestController
{
    /**
     * @Route("/access-denied-page")
     */
    public function accessDeniedAction()
    {
        throw new AccessDeniedHttpException('Test exception.');
    }
}
