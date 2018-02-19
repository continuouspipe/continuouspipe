<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Docker\DockerfileResolver;
use ContinuousPipe\Builder\Logging;
use ContinuousPipe\Builder\Request\BuildRequest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManifestFactory
{
    /**
     * @var DockerfileResolver
     */
    private $dockerfileResolver;
    /**
     * @var string
     */
    private $artifactsBucketName;
    /**
     * @var string
     */
    private $artifactsServiceAccountFilePath;
    /**
     * @var string
     */
    private $firebaseDatabaseUrl;
    /**
     * @var string
     */
    private $firebaseServiceAccountFilePath;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var string
     */
    private $riverHost;

    public function __construct(
        DockerfileResolver $dockerfileResolver,
        UrlGeneratorInterface $urlGenerator,
        string $artifactsBucketName = null,
        string $artifactsServiceAccountFilePath = null,
        string $firebaseDatabaseUrl = null,
        string $firebaseServiceAccountFilePath = null,
        string $riverHost = null
    ) {
        $this->dockerfileResolver = $dockerfileResolver;
        $this->urlGenerator = $urlGenerator;
        $this->artifactsBucketName = $artifactsBucketName;
        $this->artifactsServiceAccountFilePath = $artifactsServiceAccountFilePath;
        $this->firebaseDatabaseUrl = $firebaseDatabaseUrl;
        $this->firebaseServiceAccountFilePath = $firebaseServiceAccountFilePath;
        $this->riverHost = $riverHost;
    }

    public function create(Build $build) : array
    {
        $request = $build->getRequest();
        $buildCompleteEndpoint = sprintf(
            'https://%s%s',
            $this->riverHost,
            $this->urlGenerator->generate('complete_build', ['identifier' => $build->getIdentifier()])
        );

        return [
            'log_boundary' => $build->getIdentifier(),
            'build_complete_endpoint' => $buildCompleteEndpoint,
            'artifacts_configuration' => [
                'bucket_name' => $this->artifactsBucketName,
                'service_account' => null !== $this->artifactsServiceAccountFilePath ? \GuzzleHttp\json_decode(file_get_contents($this->artifactsServiceAccountFilePath), true) : null,
            ],
            'firebase_logging_configuration' => [
                'database_url' => $this->firebaseDatabaseUrl,
                'parent_log' => $this->getFirebaseParentLog($request->getLogging()),
                'service_account' => null !== $this->firebaseServiceAccountFilePath ? \GuzzleHttp\json_decode(file_get_contents($this->firebaseServiceAccountFilePath), true) : null,
            ],
            'auth_configs' => $this->dockerRegistryAuthConfigs($request),
            'steps' => array_map(function (BuildStepConfiguration $step) {
                $stepManifest = [
                    'read_artifacts' => array_map([$this, 'createArtifactManifest'], $step->getReadArtifacts()),
                    'write_artifacts' => array_map([$this, 'createArtifactManifest'], $step->getWriteArtifacts()),
                    'docker_file_path' => $this->dockerfileResolver->getFilePath($step->getContext()),
                ];

                if ($image = $step->getImage()) {
                    $stepManifest['image_name'] = $image->getName().':'.$image->getTag();
                }

                if (!empty($environment = $step->getEnvironment())) {
                    $stepManifest['build_args'] = $environment;
                }

                if (null !== ($context = $step->getContext())) {
                    $stepManifest['build_directory'] = $context->getRepositorySubDirectory();
                }

                if (null !== ($archiveSource = $step->getArchive())) {
                    $stepManifest['archive_source'] = [
                        'url' => $archiveSource->getUrl(),
                        'headers' => empty($archiveSource->getHeaders()) ? new \stdClass() : $archiveSource->getHeaders()
                    ];
                }

                return $stepManifest;
            }, $request->getSteps()),
        ];
    }

    private function createArtifactManifest(Artifact $artifact) : array
    {
        return [
            'identifier' => $artifact->getIdentifier(),
            'name' => $artifact->getName() ?: $artifact->getPath(),
            'path' => $artifact->getPath(),
            'persistent' => $artifact->isPersistent(),
        ];
    }

    private function dockerRegistryAuthConfigs(BuildRequest $request) : array
    {
        $authConfigs = [];

        foreach ($request->getSteps() as $step) {
            foreach ($step->getDockerRegistries() as $dockerRegistry) {
                $authConfigs[$dockerRegistry->getServerAddress()] = [
                    'username' => $dockerRegistry->getUsername(),
                    'password' => $dockerRegistry->getPassword(),
                    'email' => $dockerRegistry->getEmail(),
                ];
            }
        }

        return $authConfigs;
    }

    private function getFirebaseParentLog(Logging $logging = null)
    {
        if (null === $logging || null === ($logStream = $logging->getLogStream())) {
            return null;
        }

        return 'logs/'.$logStream->getParentLogIdentifier();
    }
}
