<?php

namespace GitHub\WebHook\Event;

use GitHub\WebHook\AbstractEvent;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

class PingEvent extends AbstractEvent
{
    /**
     * @var Repository
     *
     * @JMS\Type("GitHub\WebHook\Model\Repository")
     */
    private $repository;

    /**
     * @var User
     *
     * @JMS\Type("GitHub\WebHook\Model\User")
     */
    private $sender;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'ping';
    }
}
