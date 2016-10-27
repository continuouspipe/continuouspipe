<?php

namespace ContinuousPipe\River\WebHook;

interface WebHookClient
{
    /**
     * @param WebHook $webHook
     *
     * @throws WebHookException
     */
    public function send(WebHook $webHook);
}
