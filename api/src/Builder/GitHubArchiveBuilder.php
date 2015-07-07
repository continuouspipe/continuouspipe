<?php

namespace Builder;

use Builder\GitHub\GitHubArchive;
use Builder\GitHub\RepositoryAddressDescriptor;

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

    public function getArchive(Repository $repository)
    {
        $archiveUrl = $this->getArchiveUrl($repository);

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
