<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route(service="api.controller.user")
 */
class UserController
{
    /**
     * @Route("/user/{username}", methods={"GET"})
     * @ParamConverter("userObject", converter="user", options={"byUsername"="username"})
     * @Security("is_granted('VIEW', userObject)")
     * @View
     */
    public function getByUsernameAction(User $userObject)
    {
        return $userObject;
    }
}
