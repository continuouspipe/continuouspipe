<?php

namespace GitHub\WebHook\Setup;

use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\WebHook;

interface WebHookManager
{
    /**
     * Create a web hook for this repository.
     *
     * @param Repository $repository
     * @param WebHook    $webHook
     *
     * @return WebHook
     */
    public function setup(Repository $repository, WebHook $webHook);
}
