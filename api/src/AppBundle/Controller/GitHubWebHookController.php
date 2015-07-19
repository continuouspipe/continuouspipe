<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Event\External\CodePushedEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\GitHubRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="app.controller.github_webhook")
 */
class GitHubWebHookController
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @Route("/github/payload")
     *
     * @ParamConverter("request", converter="githubRequest")
     */
    public function payloadAction(GitHubRequest $request)
    {
        $event = $request->getEvent();
        if ($event instanceof PushEvent) {
            $this->eventBus->handle(CodePushedEvent::fromGitHubPush($event));
        } else {
            return new Response(sprintf('Event of type "%s" is not supported', $event->getType()));
        }

        return new Response('OK');
    }
}
