<?php

namespace ContinuousPipe\River\EventListener\GitHub;

use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\River\Event\GitHub\PullRequestEvent;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\Tide\StartVoter\TideStartVoter;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideFactory;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class EventuallyCreateAndStartTide
{
    /**
     * @var TideFactory
     */
    private $tideFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var TideStartVoter
     */
    private $tideStartVoter;

    /**
     * @param TideFactory     $tideFactory
     * @param MessageBus      $eventBus
     * @param ClientFactory   $gitHubClientFactory
     * @param LoggerInterface $logger
     * @param LoggerFactory   $loggerFactory
     * @param TideStartVoter  $tideStartVoter
     */
    public function __construct(TideFactory $tideFactory, MessageBus $eventBus, ClientFactory $gitHubClientFactory, LoggerInterface $logger, LoggerFactory $loggerFactory, TideStartVoter $tideStartVoter)
    {
        $this->tideFactory = $tideFactory;
        $this->eventBus = $eventBus;
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->logger = $logger;
        $this->loggerFactory = $loggerFactory;
        $this->tideStartVoter = $tideStartVoter;
    }

    /**
     * @param CodeRepositoryEvent $event
     */
    public function notify(CodeRepositoryEvent $event)
    {
        $tide = $this->tideFactory->createFromCodeReference(
            $event->getFlow(),
            $event->getCodeReference()
        );

        try {
            $context = $this->createContextFromEvent($event);

            if (!$this->tideStartVoter->vote($tide, $context)) {
                return;
            }
        } catch (TideConfigurationException $e) {
            $logger = $this->loggerFactory->from($tide->getContext()->getLog());
            $logger->append(new Text('Tide filter error: '.$e->getMessage()));
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }

    /**
     * @param CodeRepositoryEvent $event
     *
     * @return ArrayObject
     */
    private function createContextFromEvent(CodeRepositoryEvent $event)
    {
        $context = new ArrayObject([
            'code_reference' => new ArrayObject([
                'branch' => $event->getCodeReference()->getBranch(),
                'sha' => $event->getCodeReference()->getCommitSha(),
            ]),
        ]);

        if ($event instanceof PullRequestEvent) {
            $pullRequest = $event->getEvent()->getPullRequest();

            $context['pull_request'] = new ArrayObject([
                'number' => $pullRequest->getNumber(),
                'state' => $pullRequest->getState(),
                'labels' => $this->getPullRequestLabelNames($event),
            ]);
        } else {
            $context['pull_request'] = new ArrayObject([
                'number' => 0,
                'state' => '',
                'labels' => [],
            ]);
        }

        return $context;
    }

    /**
     * @param PullRequestEvent $event
     *
     * @return array
     */
    private function getPullRequestLabelNames(PullRequestEvent $event)
    {
        $user = $event->getFlow()->getContext()->getUser();
        try {
            $client = $this->gitHubClientFactory->createClientForUser($user);
        } catch (UserCredentialsNotFound $e) {
            $this->logger->warning('Unable to get pull-request labels, credentials not found', [
                'exception' => $e,
                'user' => $user,
            ]);

            return [];
        }

        $repository = $event->getEvent()->getRepository();

        try {
            $labels = $client->issue()->labels()->all(
                $repository->getOwner()->getLogin(),
                $repository->getName(),
                $event->getEvent()->getPullRequest()->getNumber()
            );
        } catch (\Exception $e) {
            $this->logger->error('Unable to get pull-request labels, the communication with the GH API wasn\'t successful', [
                'exception' => $e,
            ]);

            return [];
        }

        if (!is_array($labels)) {
            $this->logger->error('Received a non-array response from GH API', [
                'response' => $labels,
            ]);

            return [];
        }

        return array_map(function (array $label) {
            return $label['name'];
        }, $labels);
    }
}
