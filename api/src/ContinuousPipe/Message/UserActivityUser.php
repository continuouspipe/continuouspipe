<?php

namespace ContinuousPipe\Message;

use JMS\Serializer\Annotation as JMS;

class UserActivityUser
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     *
     * @var string|null
     */
    private $email;

    /**
     * @JMS\Type("string")
     *
     * @var string|null
     */
    private $displayName;

    public function __construct(string $username, string $email = null, string $displayName = null)
    {
        $this->username = $username;
        $this->email = $email;
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return null|string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
}
