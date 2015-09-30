<?php

namespace GitHub\WebHook;

class DefaultEventClassMapping extends EventClassMapping
{
    private static $defaultMapping = [
        'pull_request' => 'GitHub\\WebHook\\Event\\PullRequestEvent',
        'ping' => 'GitHub\\WebHook\\Event\\PingEvent',
        'push' => 'GitHub\\WebHook\\Event\\PushEvent',
    ];

    public function __construct()
    {
        parent::__construct(self::$defaultMapping);
    }
}
