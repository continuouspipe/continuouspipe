<?php

namespace GitHub\WebHook\Setup;

use ContinuousPipe\River\Event\GitHub\InstallationEvent;
use GitHub\Integration\InstallationRepositoryWithCacheInvalidation;
use Psr\Log\LoggerInterface;

class CacheManager
{
    /**
     * @var array
     */
    private $installationRepositories;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(array $installationRepositories, LoggerInterface $logger)
    {
        $this->installationRepositories = $installationRepositories;
        $this->logger = $logger;
    }

    public function notify(InstallationEvent $event)
    {
        foreach ($this->installationRepositories as $repository) {
            if ($repository instanceof InstallationRepositoryWithCacheInvalidation) {
                $repository->invalidate($event->getInstallation());
            } else {
                $this->logger->warning(
                    sprintf(
                        'The GitHub installation repository "%s" does not support cache invalidation.',
                        get_class($repository)
                    )
                );
            }
        }
    }
}
