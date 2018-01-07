<?php

namespace ContinuousPipe\River\Alerts;

class AlertAction
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $href;

    /**
     * @param string $type
     * @param string $title
     * @param string $href
     */
    public function __construct($type, $title, $href)
    {
        $this->type = $type;
        $this->title = $title;
        $this->href = $href;
    }
}
