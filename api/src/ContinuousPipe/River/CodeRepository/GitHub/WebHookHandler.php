<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
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
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var PullRequestDeploymentNotifier
     */
    private $pullRequestDeploymentNotifier;

    /**
     * @param TideFactory                   $tideFactory
     * @param CodeReferenceResolver         $codeReferenceResolver
     * @param MessageBus                    $eventBus
     * @param TideRepository                $tideRepository
     * @param EventStore                    $eventStore
     * @param PullRequestDeploymentNotifier $pullRequestDeploymentNotifier
     */
    public function __construct(TideFactory $tideFactory, CodeReferenceResolver $codeReferenceResolver, MessageBus $eventBus, TideRepository $tideRepository, EventStore $eventStore, PullRequestDeploymentNotifier $pullRequestDeploymentNotifier)
    {
        $this->tideFactory = $tideFactory;
        $this->codeReferenceResolver = $codeReferenceResolver;
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
        $this->eventStore = $eventStore;
        $this->pullRequestDeploymentNotifier = $pullRequestDeploymentNotifier;
    }

    /**
     * @param Flow          $flow
     * @param GitHubRequest $gitHubRequest
     *
     * @return \ContinuousPipe\River\View\Tide[]|Flow
     */
    public function handle(Flow $flow, GitHubRequest $gitHubRequest)
    {
        $event = $gitHubRequest->getEvent();
        if ($event instanceof PingEvent) {
            return $flow;
        } else if ($event instanceof PushEvent) {
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
     *
     * @return \ContinuousPipe\River\View\Tide[]
     */
    private function handlePullRequestEvent(Flow $flow, PullRequestEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPullRequestEvent($event);
        $tides = $this->tideRepository->findByCodeReference($codeReference);

        foreach ($tides as $tide) {
            $tideEvents = $this->eventStore->findByTideUuid($tide->getUuid());
            $deploymentSuccessfulEvents = array_filter($tideEvents, function (TideEvent $event) {
                return $event instanceof DeploymentSuccessful;
            });

            foreach ($deploymentSuccessfulEvents as $deploymentSuccessfulEvent) {
                $this->pullRequestDeploymentNotifier->notify($deploymentSuccessfulEvent, $event->getRepository(), $event->getPullRequest());
            }
        }

        return $tides;
    }
}
