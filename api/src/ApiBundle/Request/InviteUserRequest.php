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

    /**
     * @Assert\All({
     *     @Assert\Choice(choices = {"USER", "ADMIN"}, message = "Choose a valid permission.")
     * })
     *
     * @JMS\Type("array<string>")
     *
     * @var string[]
     */
    public $permissions;
}
