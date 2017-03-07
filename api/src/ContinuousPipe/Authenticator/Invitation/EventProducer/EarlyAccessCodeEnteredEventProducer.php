<?php

namespace ContinuousPipe\Authenticator\Invitation\EventProducer;

use ContinuousPipe\Authenticator\EarlyAccess\BypassWhiteListToggleFactory;
use ContinuousPipe\Authenticator\Invitation\Event\EarlyAccessCodeEntered;
use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EarlyAccessCodeEnteredEventProducer implements EventSubscriberInterface
{
    /**
     * @var BypassWhiteListToggleFactory
     */
    private $bypassWhiteListToggleFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        BypassWhiteListToggleFactory $bypassWhiteListToggleFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->bypassWhiteListToggleFactory = $bypassWhiteListToggleFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserCreated::EVENT_NAME => ['onUserCreated', -10],
        ];
    }

    public function onUserCreated(UserCreated $event)
    {
        $toggle = $this->bypassWhiteListToggleFactory->createFromSession();

        if ($toggle->isActive()) {
            $this->eventDispatcher->dispatch(
                EarlyAccessCodeEntered::EVENT_NAME,
                new EarlyAccessCodeEntered($event->getUser(), $toggle->getUsedEarlyAccessCode())
            );
        }
    }
}
