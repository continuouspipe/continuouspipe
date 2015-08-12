<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\GitHub\WebHookHandler;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param WebHookHandler $webHookHandler
     * @param TideRepository $tideRepository
     */
    public function __construct(WebHookHandler $webHookHandler, TideRepository $tideRepository)
    {
        $this->webHookHandler = $webHookHandler;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @Route("/web-hook/github/{uuid}", methods={"POST"}, name="web_hook_github")
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("request", converter="githubRequest")
     * @View
     */
    public function payloadAction(Flow $flow, GitHubRequest $request)
    {
        $tide = $this->webHookHandler->handle($flow, $request);

        return $this->tideRepository->find($tide->getContext()->getTideUuid());
    }
}
