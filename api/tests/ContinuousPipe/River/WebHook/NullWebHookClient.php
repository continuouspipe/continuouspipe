<?php

namespace ContinuousPipe\River\WebHook;

class NullWebHookClient implements WebHookClient
{
    /**
     * {@inheritdoc}
     */
    public function send(WebHook $webHook)
    {
    }
}
