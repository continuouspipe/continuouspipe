<?php

namespace ApiBundle\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class CreateApiKeyRequest
{
    /**
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    public $description;
}
