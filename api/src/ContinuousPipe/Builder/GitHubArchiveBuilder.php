<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\GitHub\GitHubArchive;
use ContinuousPipe\Builder\GitHub\RepositoryAddressDescriptor;
use ContinuousPipe\LogStream\Log;
use ContinuousPipe\LogStream\Logger;

class GitHubArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var RepositoryAddressDescriptor
     */
    private $addressDescriptor;

    public function __construct(RepositoryAddressDescriptor $addressDescriptor)
    {
        $this->addressDescriptor = $addressDescriptor;
    }

    public function getArchive(Repository $repository, Logger $logger)
    {
        $archiveUrl = $this->getArchiveUrl($repository);
        $logger->log(Log::output(sprintf('Got archive URL from GitHub: %s', $archiveUrl)));

        return new GitHubArchive($archiveUrl);
    }

    private function getArchiveUrl(Repository $repository)
    {
        $description = $this->addressDescriptor->getDescription($repository->getAddress());

        return sprintf(
            'https://github.com/%s/%s/archive/%s.zip',
            $description->getUsername(),
            $description->getRepository(),
            $repository->getBranch()
        );
    }
}
