<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\Flow;
use GitHub\WebHook\GitHubRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;

/**
 * @Route(service="app.controller.github_webhook")
 */
class GitHubWebHookController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @Route("/web-hook/github/{uuid}", methods={"POST"}, name="web_hook_github")
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("request", converter="githubRequest")
     * @View
     */
    public function payloadAction(Flow $flow, GitHubRequest $request)
    {
        $this->commandBus->handle(new HandleGitHubEvent(
            $flow->getUuid(),
            $request->getEvent()
        ));
    }
}
