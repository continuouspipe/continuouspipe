<?php

namespace AppBundle\Model\DataCollector;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\UserActivity\UserActivityContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserActivityContextDataCollector implements EventSubscriberInterface, UserActivityContextProvider
{
    /**
     * @var UserActivityContext
     */
    private $context;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -10]
        ];
    }

    public function __construct()
    {
        $this->context = new UserActivityContext();
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        foreach ($event->getRequest()->attributes as $name => $attribute) {
            if ($attribute instanceof Team) {
                $this->context->setTeamSlug($attribute->getSlug());
            } elseif ($attribute instanceof Flow || $attribute instanceof FlatFlow) {
                $this->context->setFlowUuid($attribute->getUuid());
                $this->context->setTeamSlug($attribute->getTeam()->getSlug());
            } elseif ($attribute instanceof Tide) {
                $this->context->setTideUuid($attribute->getUuid());
                $this->context->setFlowUuid($attribute->getFlowUuid());
                $this->context->setTeamSlug($attribute->getTeam()->getSlug());
            }
        }
    }

    public function getContext(): UserActivityContext
    {
        return $this->context;
    }
}
