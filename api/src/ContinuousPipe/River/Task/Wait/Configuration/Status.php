<?php

namespace ContinuousPipe\River\Task\Wait\Configuration;

use JMS\Serializer\Annotation as JMS;

class Status
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $context;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $state;

    /**
     * @param string $context
     * @param string $state
     */
    public function __construct($context, $state)
    {
        $this->context = $context;
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
}
