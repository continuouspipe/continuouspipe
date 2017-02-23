<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class EarlyAccessCode
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @AppBundle\Validator\Constraints\EarlyAccessCode
     */
    public $code;
}
