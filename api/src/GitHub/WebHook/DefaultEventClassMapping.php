<?php

namespace GitHub\WebHook;

class DefaultEventClassMapping extends EventClassMapping
{
    private static $defaultMapping = [
        'pull_request' => 'GitHub\\WebHook\\Event\\PullRequestEvent',
        'ping' => 'GitHub\\WebHook\\Event\\PingEvent',
        'push' => 'GitHub\\WebHook\\Event\\PushEvent',
        'status' => 'GitHub\\WebHook\\Event\\StatusEvent',
        'integration_installation' => 'GitHub\\WebHook\\Event\\IntegrationInstallationEvent',
        'installation_repositories' => 'GitHub\\WebHook\\Event\\InstallationRepositoriesEvent',
    ];

    public function __construct()
    {
        parent::__construct(self::$defaultMapping);
    }
}
