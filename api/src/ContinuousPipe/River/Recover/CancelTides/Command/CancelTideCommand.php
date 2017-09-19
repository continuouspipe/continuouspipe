<?php

namespace ContinuousPipe\River\Recover\CancelTides\Command;

use ContinuousPipe\River\Command\TideCommand;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class CancelTideCommand implements TideCommand
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
    public function __construct(Uuid $tideUuid, $username)
    {
        $this->tideUuid = $tideUuid;
        $this->username = $username;
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    /**
     * Return the username of the user who run the command.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }
}
