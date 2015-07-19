<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use Github\Client;

class GitHubRelativeFileSystem implements RelativeFileSystem
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var RepositoryDescription
     */
    private $repositoryDescription;
    /**
     * @var null|string
     */
    private $reference;

    /**
     * @param Client                $client
     * @param RepositoryDescription $repositoryDescription
     * @param string                $reference
     */
    public function __construct(Client $client, RepositoryDescription $repositoryDescription, $reference = null)
    {
        $this->client = $client;
        $this->repositoryDescription = $repositoryDescription;
        $this->reference = $reference;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filePath)
    {
        return $this->client->repo()->contents()->exists(
            $this->repositoryDescription->getUsername(),
            $this->repositoryDescription->getRepository(),
            $filePath,
            $this->reference
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        $contentsResult = $this->client->repo()->contents()->show(
            $this->repositoryDescription->getUsername(),
            $this->repositoryDescription->getRepository(),
            $filePath,
            $this->reference
        );

        $contents = base64_decode($contentsResult['content']);

        return $contents;
    }
}
