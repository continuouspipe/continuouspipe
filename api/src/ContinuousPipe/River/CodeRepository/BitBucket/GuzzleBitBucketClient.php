<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class GuzzleBitBucketClient implements BitBucketClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getReference(string $owner, string $repository, string $branch) : string
    {
        try {
            $response = $this->client->request('GET', '/2.0/repositories/'.$owner.'/'.$repository.'/refs/branches/'.$branch);
        } catch (RequestException $e) {
            $message = $e->getMessage();
            if ($e->getResponse() && $e->getResponse()->getStatusCode() == 404) {
                $message = 'The branch "'.$branch.'" is not found in the repository';
            }

            throw new BitBucketClientException($message, $e->getCode(), $e);
        }

        $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $json['target']['hash'];
    }

    public function getContents(string $owner, string $repository, string $reference, string $filePath): string
    {
        try {
            $response = $this->client->request('GET', '/1.0/repositories/'.$owner.'/'.$repository.'/src/'.$reference.'/'.$filePath);
        } catch (RequestException $e) {
            $message = $e->getMessage();
            if ($e->getResponse() && $e->getResponse()->getStatusCode() == 404) {
                $message = 'The file "'.$filePath.'" is not found in "'.$reference.'" of the repository';
            }

            throw new BitBucketClientException($message, $e->getCode(), $e);
        }

        $body = $response->getBody();
        $body->rewind();

        try {
            $json = \GuzzleHttp\json_decode($body->getContents(), true);
        } catch (\InvalidArgumentException $e) {
            throw new BitBucketClientException('Response from BitBucket is not a valid JSON document', $e->getCode(), $e);
        }

        return $json['data'];
    }
}
