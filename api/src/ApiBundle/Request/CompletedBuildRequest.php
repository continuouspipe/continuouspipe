<?php

namespace ApiBundle\Request;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CompletedBuildRequest
{
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
