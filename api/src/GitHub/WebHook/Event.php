<?php

namespace GitHub\WebHook;

use GitHub\WebHook\Model\Repository;

interface Event
{
    /**
     * Get type sent by GitHub for this event.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the repository related to this event.
     *
     * @return Repository
     */
    public function getRepository();
}
