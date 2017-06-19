<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest;
use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\Commit;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleBitBucketClient implements BitBucketClient
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(ClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function getReference(BitBucketCodeRepository $codeRepository, string $branch) : string
    {
        try {
            $response = $this->client->request('GET', '/2.0/repositories/'.$codeRepository->getApiSlug().'/refs/branches/'.$branch);
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

    public function getContents(BitBucketCodeRepository $codeRepository, string $reference, string $filePath): string
    {
        try {
            $response = $this->client->request('GET', '/1.0/repositories/'.$codeRepository->getApiSlug().'/src/'.$reference.'/'.$filePath);
        } catch (RequestException $e) {
            $message = $e->getMessage();
            if ($e->getResponse() && $e->getResponse()->getStatusCode() == 404) {
                $message = 'The file "'.$filePath.'" is not found in "'.$reference.'" of the repository';
            }

            throw new BitBucketClientException($message, $e->getCode(), $e);
        }

        $json = $this->readJson($response);

        return $json['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildStatus(BitBucketCodeRepository $codeRepository, string $reference, BuildStatus $status)
    {
        try {
            $this->client->request('POST', '/2.0/repositories/'.$codeRepository->getApiSlug().'/commit/'.$reference.'/statuses/build', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $this->serializer->serialize($status, 'json'),
            ]);
        } catch (RequestException $e) {
            throw new BitBucketClientException('Unable to update the build status', $e->getCode(), $e);
        }
    }

    public function getOpenedPullRequests(BitBucketCodeRepository $codeRepository): array
    {
        return $this->readPullRequests(
            '/2.0/repositories/'.$codeRepository->getApiSlug().'/pullrequests?state=OPEN'
        );
    }

    public function writePullRequestComment(BitBucketCodeRepository $codeRepository, string $pullRequestIdentifier, string $contents): string
    {
        try {
            $response = $this->client->request('POST', '/1.0/repositories/'.$codeRepository->getApiSlug().'/pullrequests/'.$pullRequestIdentifier.'/comments', [
                'form_params' => [
                    'content' => $contents,
                ],
            ]);
        } catch (RequestException $e) {
            throw new BitBucketClientException($e->getMessage(), $e->getCode(), $e);
        }

        $json = $this->readJson($response);

        return $json['comment_id'];
    }

    public function deletePullRequestComment(BitBucketCodeRepository $codeRepository, string $pullRequestIdentifier, string $commentIdentifier)
    {
        try {
            $this->client->request('DELETE', '/1.0/repositories/'.$codeRepository->getApiSlug().'/pullrequests/'.$pullRequestIdentifier.'/comments/'.$commentIdentifier);
        } catch (RequestException $e) {
            throw new BitBucketClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws BitBucketClientException
     *
     * @return array
     */
    private function readJson($response): array
    {
        $body = $response->getBody();
        $body->rewind();

        try {
            return \GuzzleHttp\json_decode($body->getContents(), true);
        } catch (\InvalidArgumentException $e) {
            throw new BitBucketClientException('Response from BitBucket is not a valid JSON document', $e->getCode(), $e);
        }
    }

    /**
     * @param string $link
     *
     * @return PullRequest[]
     *
     * @throws BitBucketClientException
     */
    private function readPullRequests(string $link)
    {
        try {
            $response = $this->client->request('GET', $link);
        } catch (RequestException $e) {
            throw new BitBucketClientException($e->getMessage(), $e->getCode(), $e);
        }

        $json = $this->readJson($response);
        $pullRequests = $this->serializer->deserialize(
            \GuzzleHttp\json_encode($json['values']),
            'array<'.PullRequest::class.'>',
            'json'
        );

        if (isset($json['next'])) {
            foreach ($this->readPullRequests($json['next']) as $pullRequest) {
                $pullRequests[] = $pullRequest;
            }
        }

        return $pullRequests;
    }

    public function getBranches(BitBucketCodeRepository $codeRepository)
    {
        try {
            return $this->readBranches(
                '/2.0/repositories/' . $codeRepository->getApiSlug() . '/refs/branches',
                $codeRepository->getAddress()
            );
        } catch (RequestException $e) {
            $message = $e->getMessage();
            if ($e->getResponse() && $e->getResponse()->getStatusCode() == 404) {
                $message = 'Branches not found for the repository';
            }

            throw new BitBucketClientException($message, $e->getCode(), $e);
        }
    }

    private function readBranches(string $link, string $address)
    {
        try {
            $response = $this->client->request('GET', $link);
        } catch (RequestException $e) {
            throw new BitBucketClientException($e->getMessage(), $e->getCode(), $e);
        }

        $json = $this->readJson($response);

        $branches = array_map(
            function (array $b) use ($address) {
                $branch = Branch::bitbucket($b['name'], $address);

                if (isset($b['target']['hash']) && isset($b['target']['links']['html']['href'])) {
                    return $branch->withLatestCommit(
                        new Commit(
                            $b['target']['hash'],
                            $b['target']['links']['html']['href'],
                            isset($b['target']['date']) ? new \DateTime($b['target']['date']) : null
                        )
                    );
                }

                return $branch;
            },
            $json['values']
        );

        if (isset($json['next'])) {
            return array_merge($branches, $this->readBranches($json['next'], $address));
        }

        return $branches;
    }
}
