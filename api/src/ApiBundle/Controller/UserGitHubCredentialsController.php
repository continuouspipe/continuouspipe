<?php

namespace ApiBundle\Controller;

use ContinuousPipe\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
