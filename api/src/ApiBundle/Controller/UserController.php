<?php

namespace ApiBundle\Controller;

use ContinuousPipe\User\User;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.user")
 */
class UserController
{
    /**
     * @Route("/user/{email}", methods={"GET"})
     * @ParamConverter("user", converter="user")
     * @View
     */
    public function getByEmailAction(User $user)
    {
        return $user;
    }
}
