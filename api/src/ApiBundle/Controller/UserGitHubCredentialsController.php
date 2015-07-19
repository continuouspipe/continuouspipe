<?php

namespace ApiBundle\Controller;

use ContinuousPipe\User\SecurityUser;
use ContinuousPipe\User\User;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.user_github_credentials")
 */
class UserGitHubCredentialsController
{
    /**
     * @Route("/user/{email}/credentials/github/valid", methods={"GET"})
     * @ParamConverter("user", converter="user")
     * @View
     */
    public function getValidAction(User $user)
    {
        return $user->getGitHubCredentials();
    }
}
