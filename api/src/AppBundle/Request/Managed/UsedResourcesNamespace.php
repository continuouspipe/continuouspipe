<?php

namespace AppBundle\Request\Managed;

use JMS\Serializer\Annotation as JMS;

class UsedResourcesNamespace
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("array<string,string>")
     *
     * @var string[]
     */
    private $labels;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]|null
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
