<?php

namespace ContinuousPipe\River\EventBus;

use AppBundle\Model\DataCollector\UserActivityContextProvider;
use ContinuousPipe\River\CodeRepository\BitBucket\Command\HandleBitBucketEvent;
use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\UserActivity\UserActivityContext;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class UserActivityContextDataCollectorMiddleware implements MessageBusMiddleware, UserActivityContextProvider
{
    /**
     * @var UserActivityContext
     */
    private $context;

    /**
     * @var FlowRepository
     */
    private $flowRepository;

    public function __construct(FlowRepository $flowRepository)
    {
        $this->context = new UserActivityContext();
        $this->flowRepository = $flowRepository;
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        if ($message instanceof HandleGitHubEvent || $message instanceof HandleBitBucketEvent) {
            try {
                $flow = $this->flowRepository->find($message->getFlowUuid());
                $this->context->setFlowUuid($message->getFlowUuid());
                $this->context->setTeamSlug($flow->getTeam()->getSlug());
            } catch (FlowNotFound $e) {
            }
        }

        $next($message);
    }

    public function getContext(): UserActivityContext
    {
        return $this->context;
    }
}
