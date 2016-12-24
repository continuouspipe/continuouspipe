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

    public function getReference(string $owner, string $repository, string $branch)
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
}
