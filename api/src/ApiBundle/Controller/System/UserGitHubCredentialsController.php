<?php

namespace ApiBundle\Controller\System;

use ContinuousPipe\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.system.controller.user_github_credentials")
 */
class UserGitHubCredentialsController
{
    /**
     * @Route("/user/{email}/credentials/github/valid", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"byEmail"="email"})
     * @View
     */
    public function getValidAction(User $user)
    {
        return $user->getGitHubCredentials();
    }
}
