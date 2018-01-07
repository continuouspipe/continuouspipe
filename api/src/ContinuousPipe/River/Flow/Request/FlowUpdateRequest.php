<?php

namespace ContinuousPipe\River\Flow\Request;

use JMS\Serializer\Annotation as JMS;

class FlowUpdateRequest
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $ymlConfiguration;

    /**
     * @return string
     */
    public function getYmlConfiguration()
    {
        return $this->ymlConfiguration;
    }
}
