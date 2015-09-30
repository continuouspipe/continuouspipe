<?php

namespace GitHub\WebHook\Model;

class WebHook
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var WebHookConfiguration
     */
    private $configuration;
    /**
     * @var array
     */
    private $events;
    /**
     * @var bool
     */
    private $active;

    /**
     * @param string               $name
     * @param WebHookConfiguration $configuration
     * @param array                $events
     * @param bool                 $active
     */
    public function __construct($name, WebHookConfiguration $configuration, array $events = [], $active = true)
    {
        $this->name = $name;
        $this->configuration = $configuration;
        $this->events = $events;
        $this->active = $active;
    }
}
