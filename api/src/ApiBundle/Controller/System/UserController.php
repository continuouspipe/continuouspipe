<?php

namespace ApiBundle\Controller\System;

use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route("/system", service="api.system.controller.user")
 */
class UserController
{
    /**
     * @Route("/user/{username}", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"byUsername"="username"})
     * @View
     */
    public function getByUsernameAction(User $user)
    {
        return $user;
    }
}
