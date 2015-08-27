<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\User\User;
use LogStream\Logger;
use LogStream\Node\Text;

class GitHubArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var GitHubHttpClientFactory
     */
    private $gitHubHttpClientFactory;

    /**
     * @var RemoteArchiveLocator
     */
    private $remoteArchiveLocator;

    /**
     * @param RemoteArchiveLocator $remoteArchiveLocator
     * @param GitHubHttpClientFactory $gitHubHttpClientFactory
     */
    public function __construct(RemoteArchiveLocator $remoteArchiveLocator, GitHubHttpClientFactory $gitHubHttpClientFactory)
    {
        $this->gitHubHttpClientFactory = $gitHubHttpClientFactory;
        $this->remoteArchiveLocator = $remoteArchiveLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, User $user, Logger $logger)
    {
        $httpClient = $this->gitHubHttpClientFactory->createForUser($user);
        $archiveUrl = $this->remoteArchiveLocator->getArchiveUrl($buildRequest->getRepository());

        $logger->append(new Text(sprintf('Will download code from archive: %s', $archiveUrl)));

        $packer = new ArchivePacker($httpClient);
        return $packer->createFromUrl($buildRequest->getContext(), $archiveUrl);
    }
}
