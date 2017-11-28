<?php

namespace ContinuousPipe\Billing\BillingProfile\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class UserBillingProfileCreationRequest
{
    /**
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    public $name;
}
