<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\ArtifactManager;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\DockerfileResolver;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use Google\Cloud\ServiceBuilder;
use GuzzleHttp\ClientInterface;

class ContainerBuilderAsDockerFacade implements DockerFacade
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var ArtifactManager
     */
    private $artifactManager;

    /**
     * @var DockerfileResolver
     */
    private $dockerfileResolver;

    /**
     * @var string
     */
    private $artifactBucket;
    /**
     * @var string
     */
    private $projectId;
    /**
     * @var string
     */
    private $serviceAccountPath;

    /**
     * @param ClientInterface $httpClient
     * @param ArtifactManager $artifactManager
     * @param DockerfileResolver $dockerfileResolver
     * @param string $projectId
     * @param string $artifactBucket
     * @param string $serviceAccountPath
     */
    public function __construct(ClientInterface $httpClient, ArtifactManager $artifactManager, DockerfileResolver $dockerfileResolver, string $projectId, string $artifactBucket, string $serviceAccountPath)
    {
        $this->httpClient = $httpClient;
        $this->artifactManager = $artifactManager;
        $this->dockerfileResolver = $dockerfileResolver;
        $this->artifactBucket = $artifactBucket;
        $this->projectId = $projectId;
        $this->serviceAccountPath = $serviceAccountPath;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildContext $context, Archive $archive): Image
    {
        $sourceArtifact = new Artifact(uniqid().'.tar.gz');

        try {
            $this->artifactManager->write($archive, $sourceArtifact, Archive::TAG_GZ);
        } catch (Artifact\ArtifactException $e) {
            throw new DockerException('Something went wrong while pushing the source artifact', $e->getCode(), $e);
        }

        $registryConfigCredentials = base64_encode(\GuzzleHttp\json_encode(array_map(function(DockerRegistry $dockerRegistry) {
            if ('docker.io' == ($serverAddress = $dockerRegistry->getServerAddress())) {
                $serverAddress = 'https://index.docker.io/v1/';
            }

            return [
                'serveraddress' => $serverAddress,
                'username' => $dockerRegistry->getUsername(),
                'password' => $dockerRegistry->getPassword(),
            ];
        }, $context->getDockerRegistries())));

        $dockerImageName = 'docker.io/sroze/foo-bar';
        $dockerCommandArguments = [
            'build',
            '-t', $dockerImageName,
            '--file', $this->dockerfileResolver->getFilePath($context->getContext()),
            '--pull'
        ];

        // Add build arguments
        foreach ($context->getEnvironment() as $argumentName => $argumentValue) {
            $dockerCommandArguments[] = '--build-arg';
            $dockerCommandArguments[] = $argumentName.'='.$argumentValue;
        }

        $dockerCommandArguments[] = '.';

        $response = $this->httpClient->request('post', 'https://cloudbuild.googleapis.com/v1/projects/'.$this->projectId.'/builds', [
            'json' => [
                'source' => [
                    'storageSource' => [
                        'bucket' => $this->artifactBucket,
                        'object' => $sourceArtifact->getIdentifier(),
                    ]
                ],
                'steps' => [
                    [
                        'name' => 'quay.io/continuouspipe/cloud-builder:v1',
                        'env' => [
                            'DOCKER_REGISTRY_CONFIG='.$registryConfigCredentials,
                            'PUSH_DOCKER_IMAGE='.$dockerImageName,
                        ],
                        'args' => $dockerCommandArguments,
                    ]
                ],
            ]
        ]);

        $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        $buildIdentifier = $json['metadata']['build']['id'];

        $serviceBuilder = new ServiceBuilder([
            'projectId' => $this->projectId,
            'keyFilePath' => $this->serviceAccountPath,
        ]);

        $entries = $serviceBuilder->logging()->entries([
            'filter' => 'resource.type="build" resource.labels.build_id="'.$buildIdentifier.'"'
        ]);

        foreach ($entries as $entry) {
            var_dump($entry->info()['textPayload']);
        }

        exit;
    }

    /**
     * {@inheritdoc}
     */
    public function push(PushContext $context, Image $image)
    {
        throw new DockerException('Unable to push, not supported yet!');
    }
}
