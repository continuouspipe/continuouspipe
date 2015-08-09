<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\GitHub\WebHookHandler;
use ContinuousPipe\River\Flow;
use GitHub\WebHook\GitHubRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.github_webhook")
 */
class GitHubWebHookController
{
    /**
     * @var WebHookHandler
     */
    private $webHookHandler;

    /**
     * @param WebHookHandler $webHookHandler
     */
    public function __construct(WebHookHandler $webHookHandler)
    {
        $this->webHookHandler = $webHookHandler;
    }

    /**
     * @Route("/web-hook/github/{uuid}", methods={"POST"}, name="web_hook_github")
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("request", converter="githubRequest")
     * @View
     */
    public function payloadAction(Flow $flow, GitHubRequest $request)
    {
        return $this->webHookHandler->handle($flow, $request);
    }
}
