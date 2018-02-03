<?php

namespace ContinuousPipe\River\Event;

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
     * @JMS\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @param Uuid $tideUuid
     * @param string $username
     */
    public function __construct(Uuid $tideUuid, string $username = null)
    {
        $this->tideUuid = $tideUuid;
        $this->username = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * Return the username of the user who triggered the event.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }
}
