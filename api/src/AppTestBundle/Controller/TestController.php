<?php

namespace AppTestBundle\Controller;

use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\CommentEvent;
use ContinuousPipe\River\CodeRepository\BitBucket\Command\HandleBitBucketEvent;
use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\Command\StartTideCommand;
use GitHub\WebHook\Event\PingEvent;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route(path="/test", service="river.controllers.test_controller")
 */
class TestController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @Route("/access-denied-page")
     */
    public function accessDeniedAction()
    {
        throw new AccessDeniedHttpException('Test exception.');
    }

    /**
     * @Route("/tide/{uuid}/operation-failed")
     * @ParamConverter("tide", converter="tide", options={"identifier"="uuid"})
     */
    public function tideOperationFailedAction()
    {
        throw new \RuntimeException('Tide operation failed exception.');
    }

    /**
     * @Route("/github/webhook/flow/{uuid}/operation-failed")
     */
    public function gitHubWebhookOperationFailedAction($uuid)
    {
        $this->commandBus->handle(new HandleGitHubEvent(
            Uuid::fromString($uuid),
            new PingEvent()
        ));

        throw new \RuntimeException('GitHub webhook processing failed exception.');
    }

    /**
     * @Route("/bitbucket/webhook/flow/{uuid}/operation-failed")
     */
    public function bitBucketWebhookOperationFailedAction($uuid)
    {
        $this->commandBus->handle(new HandleBitBucketEvent(
            Uuid::fromString($uuid),
            new CommentEvent()
        ));

        throw new \RuntimeException('BitBucket webhook processing failed exception.');
    }

    /**
     * @Route("/worker/tide-command/{uuid}/operation-failed")
     */
    public function tideCommandOperationFailedAction($uuid)
    {
        $this->commandBus->handle(new StartTideCommand(Uuid::fromString($uuid)));

        throw new \RuntimeException('Worker operation failed exception.');
    }
}
