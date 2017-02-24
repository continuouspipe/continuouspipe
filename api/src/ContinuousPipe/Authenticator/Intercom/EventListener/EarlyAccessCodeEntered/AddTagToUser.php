<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\EarlyAccessCodeEntered;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Invitation\Event\EarlyAccessCodeEntered;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddTagToUser implements EventSubscriberInterface
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EarlyAccessCodeEntered::EVENT_NAME => 'onEarlyAccessCodeEntered',
        ];
    }

    public function __construct(IntercomClient $intercomClient)
    {
        $this->intercomClient = $intercomClient;
    }

    public function onEarlyAccessCodeEntered(EarlyAccessCodeEntered $event)
    {
        $tagName = $event->getEarlyAccessCode()->code();
        $users = [
            ['id' => $event->getUser()->getUsername()]
        ];
        $this->intercomClient->tagUsers($tagName, $users);
    }
}
