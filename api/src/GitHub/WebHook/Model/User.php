<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class User
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $login;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login ?: $this->name;
    }
}
