<?php

namespace ContinuousPipe\River\Tide\Request;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class TideCreationRequest
{
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $branch;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $sha1;

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @return string
     */
    public function getSha1()
    {
        return $this->sha1;
    }
}
