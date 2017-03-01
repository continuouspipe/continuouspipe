<?php

namespace ContinuousPipe\River\EventBus;

use AppBundle\Model\DataCollector\UserActivityContextProvider;
use ContinuousPipe\River\Command\FlowCommand;
use ContinuousPipe\River\Command\TideCommand;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Flow\Event\FlowEvent;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\TideRepository;
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

    /**
     * @var TideRepository
     */
    private $tideRepository;

    public function __construct(FlowRepository $flowRepository, TideRepository $tideRepository)
    {
        $this->context = new UserActivityContext();
        $this->flowRepository = $flowRepository;
        $this->tideRepository = $tideRepository;
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        if ($message instanceof FlowEvent || $message instanceof FlowCommand) {
            try {
                $this->context->setFlowUuid($message->getFlowUuid());

                $flow = $this->flowRepository->find($message->getFlowUuid());
                $this->context->setTeamSlug($flow->getTeam()->getSlug());
            } catch (FlowNotFound $e) {
            }
        }

        if ($message instanceof TideEvent || $message instanceof TideCommand) {
            try {
                $this->context->setTideUuid($message->getTideUuid());

                $tide = $this->tideRepository->find($message->getTideUuid());
                $this->context->setFlowUuid($tide->getFlowUuid());
                $this->context->setTeamSlug($tide->getTeam()->getSlug());
            } catch (TideNotFound $e) {
            }
        }

        $next($message);
    }

    public function getContext(): UserActivityContext
    {
        return $this->context;
    }
}
