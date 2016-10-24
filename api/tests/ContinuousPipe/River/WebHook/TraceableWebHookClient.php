<?php

namespace ContinuousPipe\River\WebHook;

class TraceableWebHookClient implements WebHookClient
{
    /**
     * @var WebHook[]
     */
    private $webHooks = [];

    /**
     * @var WebHookClient
     */
    private $decoratedClient;

    /**
     * @param WebHookClient $decoratedClient
     */
    public function __construct(WebHookClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function send(WebHook $webHook)
    {
        $this->decoratedClient->send($webHook);

        $this->webHooks[] = $webHook;
    }

    /**
     * @return WebHook[]
     */
    public function getWebHooks(): array
    {
        return $this->webHooks;
    }
}
