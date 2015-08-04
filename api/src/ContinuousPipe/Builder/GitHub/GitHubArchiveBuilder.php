<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Repository;
use LogStream\Logger;
use LogStream\Node\Text;

class GitHubArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var RepositoryAddressDescriptor
     */
    private $addressDescriptor;

    /**
     * @param RepositoryAddressDescriptor $addressDescriptor
     */
    public function __construct(RepositoryAddressDescriptor $addressDescriptor)
    {
        $this->addressDescriptor = $addressDescriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(Repository $repository, Logger $logger)
    {
        $archiveUrl = $this->getArchiveUrl($repository);
        $logger->append(new Text(sprintf('Got archive URL from GitHub: %s', $archiveUrl)));

        return new GitHubArchive($archiveUrl);
    }

    /**
     * @param Repository $repository
     *
     * @return string
     *
     * @throws InvalidRepositoryAddress
     */
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
