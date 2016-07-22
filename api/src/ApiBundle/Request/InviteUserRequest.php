<?php

namespace ApiBundle\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class InviteUserRequest
{
    /**
     * @Assert\NotBlank
     * @Assert\Email
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    public $email;
}
