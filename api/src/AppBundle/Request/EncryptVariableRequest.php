<?php

namespace AppBundle\Request;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class EncryptVariableRequest
{
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $plain;

    /**
     * @return string
     */
    public function getPlain()
    {
        return $this->plain;
    }
}
