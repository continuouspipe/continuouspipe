<?php

namespace ApiBundle\Controller\System;

use ContinuousPipe\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.system.controller.user")
 */
class UserController
{
    /**
     * @Route("/user/{email}", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"byEmail"="email"})
     * @View
     */
    public function getByEmailAction(User $user)
    {
        return $user;
    }
}
