<?php

namespace GitHub\WebHook;

interface Event
{
    /**
     * Get type sent by GitHub for this event.
     *
     * @return string
     */
    public function getType();
}
