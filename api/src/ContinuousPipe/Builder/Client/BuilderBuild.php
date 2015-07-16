<?php

namespace ContinuousPipe\Builder\Client;

use JMS\Serializer\Annotation as JMS;

class BuilderBuild
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
