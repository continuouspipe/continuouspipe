<?php

namespace AppTestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route(path="/test", service="river.controllers.test_controller")
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

    /**
     * @Route("/tide/{uuid}/operation-failed")
     * @ParamConverter("tide", converter="tide", options={"identifier"="uuid"})
     */
    public function tideOperationFailedAction()
    {
        throw new \RuntimeException('Tide operation failed exception.');
    }
}
