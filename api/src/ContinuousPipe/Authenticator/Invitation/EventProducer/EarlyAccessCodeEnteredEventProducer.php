<?php

namespace ContinuousPipe\Authenticator\Invitation\EventProducer;

use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessToggleFactory;
use ContinuousPipe\Authenticator\Invitation\Event\EarlyAccessCodeEntered;
use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EarlyAccessCodeEnteredEventProducer implements EventSubscriberInterface
{
    /**
     * @var EarlyAccessToggleFactory
     */
    private $earlyAccessToggleFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserCreated::EVENT_NAME => 'onUserCreated',
        ];
    }

    public function __construct(EarlyAccessToggleFactory $earlyAccessToggleFactory, EventDispatcherInterface $eventDispatcher)
    {
        $this->earlyAccessToggleFactory = $earlyAccessToggleFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onUserCreated(UserCreated $event)
    {
        $toggle = $this->earlyAccessToggleFactory->createFromSession();
        if ($toggle->isActive()) {
            $event = new EarlyAccessCodeEntered($event->getUser(), $toggle->getUsedEarlyAccessCode());
            $this->eventDispatcher->dispatch(EarlyAccessCodeEntered::EVENT_NAME, $event);
        }
    }
}
