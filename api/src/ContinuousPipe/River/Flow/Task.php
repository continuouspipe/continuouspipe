<?php

namespace ContinuousPipe\River\Flow;

use JMS\Serializer\Annotation as JMS;

class Task
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $context;

    /**
     * @param string $name
     * @param array  $context
     */
    public function __construct($name, array $context = [])
    {
        $this->name = $name;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
