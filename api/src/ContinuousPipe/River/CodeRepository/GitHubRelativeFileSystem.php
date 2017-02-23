<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;
use Github\Client;
use GuzzleHttp\Exception\RequestException;

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
        try {
            return $this->client->repo()->contents()->exists(
                $this->repositoryDescription->getUsername(),
                $this->repositoryDescription->getRepository(),
                $filePath,
                $this->reference
            );
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        try {
            $contentsResult = $this->client->repo()->contents()->show(
                $this->repositoryDescription->getUsername(),
                $this->repositoryDescription->getRepository(),
                $filePath,
                $this->reference
            );
        } catch (RequestException $e) {
            throw new FileNotFound($e->getMessage(), $e->getCode(), $e);
        }

        if (!isset($contentsResult['content'])) {
            throw new FileNotFound('The answer from GitHub was not understandable for the file '.$filePath);
        }

        if (false === ($contents = base64_decode($contentsResult['content']))) {
            throw new FileNotFound(sprintf(
                'Unable to decode base64 content of file "%s"',
                $filePath
            ));
        }

        return $contents;
    }
}
