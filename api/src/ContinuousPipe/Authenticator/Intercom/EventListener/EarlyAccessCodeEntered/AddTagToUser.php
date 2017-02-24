<?php

namespace ContinuousPipe\Authenticator\Intercom\EventListener\EarlyAccessCodeEntered;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Client\IntercomException;
use ContinuousPipe\Authenticator\Invitation\Event\EarlyAccessCodeEntered;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddTagToUser implements EventSubscriberInterface
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(IntercomClient $intercomClient, LoggerInterface $logger)
    {
        $this->intercomClient = $intercomClient;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EarlyAccessCodeEntered::EVENT_NAME => 'onEarlyAccessCodeEntered',
        ];
    }

    public function onEarlyAccessCodeEntered(EarlyAccessCodeEntered $event)
    {
        try {
            $this->intercomClient->tagUsers(
                $tagName = $event->getEarlyAccessCode()->code(),
                [
                    ['id' => $event->getUser()->getUsername()]
                ]
            );
        } catch (IntercomException $e) {
            $this->logger->error('Unable to tag a user in Intercom', [
                'exception' => $e,
            ]);
        }
    }
}
