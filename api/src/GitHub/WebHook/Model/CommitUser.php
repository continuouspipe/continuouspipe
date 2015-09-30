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
}
