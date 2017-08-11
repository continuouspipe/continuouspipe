<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use Github\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

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
            return $this->gitHubExists(
                $this->repositoryDescription->getUsername(),
                $this->repositoryDescription->getRepository(),
                $filePath,
                $this->reference
            );
        } catch (RequestException $e) {
            if (null !== ($response = $e->getResponse())) {
                if ($response->getStatusCode() == 404) {
                    return false;
                }
            }

            throw new FileException(
                sprintf('Unable to read file "%s". Response from GitHub: %s', $filePath, $this->formatException($e)),
                $e->getCode(),
                $e
            );
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
            if (null !== ($response = $e->getResponse())) {
                if ($response->getStatusCode() == 404) {
                    throw new FileNotFound($e->getMessage(), $e->getCode(), $e);
                }
            }

            throw new FileException(
                sprintf('Unable to read file "%s". Response from GitHub: %s', $filePath, $this->formatException($e)),
                $e->getCode(),
                $e
            );
        }

        if (!isset($contentsResult['content'])) {
            throw new FileException('The answer from GitHub was not understandable for the file '.$filePath);
        }

        if (false === ($contents = base64_decode($contentsResult['content']))) {
            throw new FileException(sprintf(
                'Unable to decode base64 content of file "%s"',
                $filePath
            ));
        }

        return $contents;
    }

    private function gitHubExists($username, $repository, $path, $reference = null)
    {
        $contentsApi = $this->client->repo()->contents();

        $url = 'repos/'.rawurlencode($username).'/'.rawurlencode($repository).'/contents';
        if (null !== $path) {
            $url .= '/'.rawurlencode($path);
        }

        $method = (new \ReflectionObject($contentsApi))->getMethod('head');
        $method->setAccessible(true);
        $response = $method->invoke($contentsApi, $url, [
            'ref' => $reference,
        ]);

        return $response->getStatusCode() == 200;
    }

    private function formatException(RequestException $e)
    {
        if (null !== ($response = $e->getResponse())) {
            return $response->getStatusCode() . ' ' . $response->getReasonPhrase();
        }

        return $e->getMessage();
    }
}
