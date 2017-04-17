<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\ArtifactManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;

class HttpGoogleContainerBuildClient implements GoogleContainerBuilderClient
{
    /**
     * @var ArchiveBuilder
     */
    private $archiveBuilder;

    /**
     * @var ArtifactManager
     */
    private $artifactManager;

    /**
     * @var ClientInterface
     */
    private $googleHttpClient;

    /**
     * @var ManifestFactory
     */
    private $manifestFactory;

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
        ArchiveBuilder $archiveBuilder,
        ArtifactManager $artifactManager,
        ClientInterface $googleHttpClient,
        ManifestFactory $manifestFactory,
        string $googleProjectId,
        string $googleSourceArtifactBucket,
        int $maximumAllowedBuildTime = 3600
    ) {
        $this->archiveBuilder = $archiveBuilder;
        $this->artifactManager = $artifactManager;
        $this->googleHttpClient = $googleHttpClient;
        $this->manifestFactory = $manifestFactory;
        $this->googleProjectId = $googleProjectId;
        $this->googleSourceArtifactBucket = $googleSourceArtifactBucket;
        $this->maximumAllowedBuildTime = $maximumAllowedBuildTime;
    }

    public function createFromRequest(Build $build): GoogleContainerBuild
    {
        try {
            $sourceArchive = $this->archiveBuilder->createArchive($build->getRequest()->getSteps()[0]);
            $sourceArchive->writeFile('continuouspipe.build-manifest.json', \GuzzleHttp\json_encode(
                $this->manifestFactory->create($build)
            ));
        } catch (Archive\ArchiveException $e) {
            throw new GoogleContainerBuilderException('Something went wrong while creating the source archive', $e->getCode(), $e);
        }

        $sourceArtifact = new Artifact($build->getIdentifier() . '.tar.gz');

        try {
            $this->artifactManager->write($sourceArchive, $sourceArtifact, Archive::TAG_GZ);
        } catch (Artifact\ArtifactException $e) {
            throw new GoogleContainerBuilderException('Unable to create the source artifact', $e->getCode(), $e);
        }

        try {
            $response = $this->googleHttpClient->request(
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
                                'name' => 'quay.io/continuouspipe/cloud-builder:v3',
                                'args' => [
                                    // Delete the manifest file once read
                                    '-delete-manifest',
                                ]
                            ]
                        ],
                        'timeout' => ((string) $this->maximumAllowedBuildTime).'s',
                    ]
                ]
            );
        } catch (ClientException $e) {
            throw new GoogleContainerBuilderException('The build was not successfully started', $e->getCode(), $e);
        }

        try {
            $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            if (!isset($json['metadata']['build']['id'])) {
                throw new \InvalidArgumentException('The identifier of the build is not returned by GCB');
            }
        } catch (\InvalidArgumentException $e) {
            throw new GoogleContainerBuilderException('Something went wrong while creating the build', $e->getCode(), $e);
        }

        return new GoogleContainerBuild(
            $json['metadata']['build']['id']
        );
    }

    public function fetchStatus(GoogleContainerBuild $build): GoogleContainerBuildStatus
    {
        try {
            $response = $this->googleHttpClient->request(
                'get',
                'https://cloudbuild.googleapis.com/v1/projects/' . $this->googleProjectId . '/builds/' . $build->getIdentifier()
            );
        } catch (ClientException $e) {
            throw new GoogleContainerBuilderException('Something went wrong while loading the status of the build', $e->getCode(), $e);
        }

        try {
            $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            if (!isset($json['status'])) {
                throw new \InvalidArgumentException('No status found in GCB\'s response');
            }
        } catch (\InvalidArgumentException $e) {
            throw new GoogleContainerBuilderException('The response from GCB was not understandable', $e->getCode(), $e);
        }

        return new GoogleContainerBuildStatus(
            $json['status'],
            isset($json['statusDetail']) ? $json['statusDetail'] : ''
        );
    }
}
