<?php

namespace AppBundle\Controller;

use GitHub\WebHook\GitHubRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class GitHubWebHookController
{
    /**
     * @Route("/github/payload")
     *
     * @ParamConverter("request", converter="githubRequest")
     */
    public function payloadAction(GitHubRequest $request)
    {
        var_dump($request);

        return new Response('OK');
    }
}
