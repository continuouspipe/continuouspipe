<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class TideCancelled implements TideEvent
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var User
     */
    private $user;

    /**
     * @param Uuid $tideUuid
     * @param User $user
     */
    public function __construct(Uuid $tideUuid, User $user)
    {
        $this->tideUuid = $tideUuid;
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * Return the user who triggered the event.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
