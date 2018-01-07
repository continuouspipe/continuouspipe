<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class CommitUser
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
     * @var string
     */
    private $email;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    public function __construct(string $username, string $email, string $name)
    {
        $this->username = $username;
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
