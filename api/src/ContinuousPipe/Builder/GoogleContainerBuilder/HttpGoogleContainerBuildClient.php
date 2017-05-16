<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\ArtifactManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Inviqa\LaunchDarklyBundle\Client\ExplicitUser\StaticClient;
use LaunchDarkly\LDUser;

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
            if (StaticClient::variation('use-synchronous-gcb-build', new LDUser($this->getUserKey($build)), false)) {
                $sourceArchive = new Archive\FileSystemArchive(Archive\FileSystemArchive::createDirectory('mani-only'));
                $gcbBuilderVersion = 'v5';
            } else {
                $sourceArchive = $this->archiveBuilder->createArchive($build->getRequest()->getSteps()[0]);
                $gcbBuilderVersion = 'v4';
            }
            $mani = \GuzzleHttp\json_encode(
                $this->manifestFactory->create($build)
            );
            $sourceArchive->writeFile('continuouspipe.build-manifest.json', $mani);
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
                                'name' => 'quay.io/continuouspipe/cloud-builder:' . $gcbBuilderVersion,
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

        return new GoogleContainerBuildStatus($json['status']);
    }

    private function getUserKey(Build $build)
    {
        $steps = $build->getRequest()->getSteps();
        if (!isset($steps[0])) {
            return 'builder';
        }

        $image = $steps[0]->getImage();
        if (!isset($image)) {
            return 'builder';
        }

        $imageName = $image->getName();
        if (!isset($imageName)) {
            return 'builder';
        }

        return $imageName;
    }
}
