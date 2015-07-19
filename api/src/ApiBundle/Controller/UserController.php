<?php

namespace ApiBundle\Controller;

use ContinuousPipe\User\SecurityUser;
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
     * @ParamConverter("securityUser", converter="security_user")
     * @View
     */
    public function getByEmailAction(SecurityUser $securityUser)
    {
        return $securityUser->getUser();
    }
}
