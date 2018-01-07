<?php

namespace ContinuousPipe\River\WebHook;

class HookableWebHookClient implements WebHookClient
{
    /**
     * @var WebHookClient
     */
    private $decoratedClient;

    /**
     * @var callable[]
     */
    private $hooks = [];

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
        foreach ($this->hooks as $hook) {
            $webHook = $hook($webHook);
        }

        return $this->decoratedClient->send($webHook);
    }

    /**
     * @param callable $hook
     */
    public function addHook(callable $hook)
    {
        $this->hooks[] = $hook;
    }
}
