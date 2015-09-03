<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View\TideRepository;
use GitHub\WebHook\Event\PingEvent;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\GitHubRequest;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class WebHookHandler
{
    /**
     * @var TideFactory
     */
    private $tideFactory;

    /**
     * @var CodeReferenceResolver
     */
    private $codeReferenceResolver;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param TideFactory                   $tideFactory
     * @param CodeReferenceResolver         $codeReferenceResolver
     * @param MessageBus                    $eventBus
     * @param TideRepository                $tideRepository
     */
    public function __construct(
        TideFactory $tideFactory,
        CodeReferenceResolver $codeReferenceResolver,
        MessageBus $eventBus,
        TideRepository $tideRepository
    ) {
        $this->tideFactory = $tideFactory;
        $this->codeReferenceResolver = $codeReferenceResolver;
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param Flow          $flow
     * @param GitHubRequest $gitHubRequest
     *
     * @return \ContinuousPipe\River\View\Tide[]|Flow|null
     */
    public function handle(Flow $flow, GitHubRequest $gitHubRequest)
    {
        $event = $gitHubRequest->getEvent();
        if ($event instanceof PingEvent) {
            return $flow;
        } elseif ($event instanceof PushEvent) {
            return $this->handlePushEvent($flow, $event);
        } elseif ($event instanceof PullRequestEvent) {
            return $this->handlePullRequestEvent($flow, $event);
        }

        throw new UnsupportedMediaTypeHttpException(sprintf(
            'Unsupported request of type "%s"',
            $gitHubRequest->getEvent()->getType()
        ));
    }

    /**
     * @param Flow      $flow
     * @param PushEvent $event
     *
     * @return \ContinuousPipe\River\View\Tide[]
     */
    private function handlePushEvent(Flow $flow, PushEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPushEvent($event);
        $tide = $this->tideFactory->createFromCodeReference($flow, $codeReference);

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return [
            $this->tideRepository->find($tide->getUuid()),
        ];
    }

    /**
     * @param Flow             $flow
     * @param PullRequestEvent $event
     */
    private function handlePullRequestEvent(Flow $flow, PullRequestEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPullRequestEvent($event);

        if ($event->getAction() == PullRequestEvent::ACTION_OPENED) {
            $this->eventBus->handle(new PullRequestOpened($event, $codeReference));
        } else if ($event->getAction() == PullRequestEvent::ACTION_CLOSED) {
            $this->eventBus->handle(new PullRequestClosed($event, $codeReference));
        }
    }
}
