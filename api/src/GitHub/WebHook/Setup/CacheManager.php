<?php

namespace GitHub\WebHook\Setup;

use ContinuousPipe\River\Event\GitHub\IntegrationInstallationDeleted;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenResolver;
use GitHub\Integration\RedisCache\PredisCachedInstallationRepository;
use GitHub\Integration\RedisCache\PredisCachedInstallationTokenResolver;

class CacheManager
{
    /**
     * @var InstallationTokenResolver
     */
    private $installationTokenResolver;

    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    public function __construct(
        InstallationTokenResolver $installationTokenResolver,
        InstallationRepository $installationRepository
    ) {
        $this->installationTokenResolver = $installationTokenResolver;
        $this->installationRepository = $installationRepository;
    }

    public function notify(IntegrationInstallationDeleted $event)
    {
        if ($this->installationTokenResolver instanceof PredisCachedInstallationTokenResolver) {
            $this->installationTokenResolver->invalidate($event->getInstallation());
        }

        if ($this->installationRepository instanceof PredisCachedInstallationRepository) {
            $this->installationRepository->invalidate($event->getInstallation());
        }
    }
}
