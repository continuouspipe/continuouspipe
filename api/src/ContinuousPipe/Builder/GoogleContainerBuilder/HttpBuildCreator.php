<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\GoogleContainerBuilder\Credentials\GuzzleHttpClientFactory;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class HttpBuildCreator implements BuildCreator
{
    /**
     * @var GuzzleHttpClientFactory
     */
    private $httpClientFactory;

    /**
     * @var string
     */
    private $googleProjectId;

    /**
     * @var string
     */
    private $googleSourceArtifactBucket;

    /**
     * @var int
     */
    private $maximumAllowedBuildTime;

    public function __construct(
        GuzzleHttpClientFactory $httpClientFactory,
        string $googleProjectId,
        string $googleSourceArtifactBucket,
        int $maximumAllowedBuildTime = 3600
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->googleProjectId = $googleProjectId;
        $this->googleSourceArtifactBucket = $googleSourceArtifactBucket;
        $this->maximumAllowedBuildTime = $maximumAllowedBuildTime;
    }

    public function startBuild(Artifact $sourceArtifact, string $gcbBuilderVersion): ResponseInterface
    {
        return $this->httpClientFactory->create()->request(
            'post',
            'https://cloudbuild.googleapis.com/v1/projects/' . $this->googleProjectId . '/builds',
            [
                'json' => [
                    'source' => [
                        'storageSource' => [
                            'bucket' => $this->googleSourceArtifactBucket,
                            'object' => $sourceArtifact->getIdentifier(),
                        ]
                    ],
                    'steps' => [
                        [
                            'name' => 'gcr.io/continuous-pipe-1042/cloud-builder:' . $gcbBuilderVersion,
                            'args' => [
                                // Delete the manifest file once read
                                '-delete-manifest',
                            ]
                        ]
                    ],
                    'timeout' => ((string) $this->maximumAllowedBuildTime) . 's',
                ]
            ]
        );
    }
}
