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
use GuzzleHttp\Exception\ClientException;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Raw;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class ContainerBuilderAsDockerFacade implements DockerFacade
{
    const BOUNDARY = 'ed8LSPHFweoprhXzhXC6VE5TsdaMjeIPleGTvDZs';
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
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ContainerBuilderAsDockerFacade constructor.
     * @param ClientInterface $httpClient
     * @param ArtifactManager $artifactManager
     * @param DockerfileResolver $dockerfileResolver
     * @param string $projectId
     * @param string $artifactBucket
     * @param string $serviceAccountPath
     * @param LoggerFactory $loggerFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $httpClient,
        ArtifactManager $artifactManager,
        DockerfileResolver $dockerfileResolver,
        string $projectId,
        string $artifactBucket,
        string $serviceAccountPath,
        LoggerFactory $loggerFactory,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->artifactManager = $artifactManager;
        $this->dockerfileResolver = $dockerfileResolver;
        $this->artifactBucket = $artifactBucket;
        $this->projectId = $projectId;
        $this->serviceAccountPath = $serviceAccountPath;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildContext $context, Archive $archive): Image
    {
        $outerLogger = $this->getBuildOuterLogger($context);
        $buildLogger = $outerLogger->child(new Raw());

        try {
            // Create the build manifest
            $manifest = [
                'name' => $this->getImageName($context->getImage()),
                'log_boundary' => self::BOUNDARY,
                'docker_file_path' =>  $this->dockerfileResolver->getFilePath($context->getContext()),
            ];

            if (!empty($buildArgs = $context->getEnvironment())) {
                $manifest['build_args'] = $buildArgs;
            }

            if (!empty($authConfigs = $this->dockerRegistryAuthConfigs($context))) {
                $manifest['auth_configs'] = $authConfigs;
            }

            $archive->writeFile('continuouspipe.build-manifest.json', \GuzzleHttp\json_encode($manifest));

            $sourceArtifact = $this->writeSourceArtifact($archive);
            $buildIdentifier = $this->requestBuild($sourceArtifact);

            $serviceBuilder = new ServiceBuilder([
                'projectId' => $this->projectId,
                'keyFilePath' => $this->serviceAccountPath,
            ]);

            $completed = false;
            $lastLog = null;
            $buildLogger->child(new Text('Building image with GCB...' . "\n"));
            $closureLogger = $this->nullLogger();
            $startTime = time();
            while (false === $completed) {
                if (true === $this->checkBuildStatus($buildIdentifier, $outerLogger, $context)) {
                    return $context->getImage();
                }

                try {
                    $entries = $serviceBuilder->logging()->entries(['filter' => $this->createFilter($buildIdentifier, $lastLog)]);

                    foreach ($entries as $entry) {
                        $log = $entry->info();
                        list($closureLogger, $outerLogger) = $this->log($context, $log, $closureLogger, $buildLogger, $outerLogger);
                        $lastLog = $log;
                    }
                } catch (\Exception $e) {
                    //Catches errors from requesting the logs and moves onto next request
                }

                if ($startTime + 3600 < time()) {
                    throw new DockerException('Build took longer than 60 minutes!');
                }
                sleep(10);
            }
        } catch (DockerException $e) {
            $outerLogger->updateStatus(Log::FAILURE);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function push(PushContext $context, Image $image)
    {
        //throw new DockerException('Unable to push, not supported yet!');
    }

    private function getImageName(Image $image) : string
    {
        return $image->getName() . ':' . $image->getTag();
    }

    private function basicLogger(Logger $logger)
    {
        return function (array $log) use ($logger) {
            if ($log['textPayload'] != self::BOUNDARY.':START'
                && $log['textPayload'] != self::BOUNDARY.':PUSH'
                && $log['textPayload'] != 'Login Succeeded'
                && $log['textPayload'] != self::BOUNDARY.':BUILD') {
                $logger->child(new Text($log['textPayload'] . "\n"));
            }
        };
    }

    private function nullLogger()
    {
        return function () {
            return;
        };
    }

    /**
     * @param BuildContext $context
     * @param $log
     * @param $closureLogger
     * @param Logger $buildLogger
     * @param Logger $outerLogger
     * @return array
     */
    private function log(BuildContext $context, $log, $closureLogger, Logger $buildLogger, Logger $outerLogger)
    {
        if (isset($log['textPayload'])) {
            $closureLogger($log);

            if (in_array($log['textPayload'], [self::BOUNDARY . ':START', self::BOUNDARY . ':BUILD'])) {
                $closureLogger = $this->basicLogger($buildLogger);
            }

            if ($log['textPayload'] == self::BOUNDARY . ':PUSH') {
                $outerLogger->updateStatus(Log::SUCCESS);
                $outerLogger = $this->loggerFactory->fromId($context->getLogStreamIdentifier())->child(
                    new Text(
                        sprintf(
                            'Pushing Docker image <code>%s</code>',
                            $this->getImageName($context->getImage())
                        )
                    )
                )->updateStatus(Log::RUNNING);
                $closureLogger = $this->basicLogger($outerLogger->child(new Raw()));
            }
        }

        return [$closureLogger, $outerLogger];
    }

    /**
     * @param $buildIdentifier
     * @param $lastLog
     * @return string
     */
    private function createFilter($buildIdentifier, $lastLog)
    {
        $filter = 'resource.type="build" resource.labels.build_id="' . $buildIdentifier . '"';

        if (null !== $lastLog) {
            $filter .= ' (timestamp>"' . $lastLog['timestamp'] . '" OR (timestamp="' . $lastLog['timestamp'] . '" insertId>"' . $lastLog['insertId'] . '"))';
            return $filter;
        }
        return $filter;
    }

    private function writeSourceArtifact(Archive $archive) : Artifact
    {
        $sourceArtifact = new Artifact(uniqid() . '.tar.gz');

        try {
            $this->artifactManager->write($archive, $sourceArtifact, Archive::TAG_GZ);
        } catch (Artifact\ArtifactException $e) {
            throw new DockerException('Something went wrong while pushing the source artifact', $e->getCode(), $e);
        }

        return $sourceArtifact;
    }

    private function getBuildOuterLogger(BuildContext $context) : Logger
    {
        $mainLogger = $this->loggerFactory->fromId($context->getLogStreamIdentifier());

        return $mainLogger->child(
            new Text(
                $context->getImage() === null
                    ? 'Building Docker image'
                    : sprintf('Building Docker image <code>%s</code>', $this->getImageName($context->getImage()))
            )
        )->updateStatus(Log::RUNNING);
    }

    private function dockerRegistryAuthConfigs(BuildContext $context) : array
    {
        $authConfigs = [];

        foreach ($context->getDockerRegistries() as $dockerRegistry) {
            $authConfigs[$dockerRegistry->getServerAddress()] = [
                'username' => $dockerRegistry->getUsername(),
                'password' => $dockerRegistry->getPassword(),
                'email' => $dockerRegistry->getEmail(),
            ];
        }

        return $authConfigs;
    }

    private function requestBuild(Artifact $sourceArtifact) : string
    {
        try {
            $response = $this->httpClient->request(
                'post',
                'https://cloudbuild.googleapis.com/v1/projects/' . $this->projectId . '/builds',
                [
                    'json' => [
                        'source' => [
                            'storageSource' => [
                                'bucket' => $this->artifactBucket,
                                'object' => $sourceArtifact->getIdentifier(),
                            ]
                        ],
                        'steps' => [
                            [
                                'name' => 'quay.io/continuouspipe/cloud-builder:v2',
                            ]
                        ],
                        'timeout' => '1800s',
                    ]
                ]
            );
        } catch (ClientException $e) {
            throw new DockerException('The build was not successfully started', $e->getCode(), $e);
        }

        $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        $buildIdentifier = $json['metadata']['build']['id'];

        return $buildIdentifier;
    }

    /**
     * @param $buildIdentifier
     * @param $outerLogger
     * @return bool
     * @throws DockerException
     */
    private function checkBuildStatus(string $buildIdentifier, Logger $outerLogger, $context)
    {
        $response = $this->httpClient->request(
            'get',
            'https://cloudbuild.googleapis.com/v1/projects/' . $this->projectId . '/builds/' . $buildIdentifier
        );
        $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        $status = isset($json['status']) ? $json['status'] : '';
        if ($status == 'SUCCESS') {
            $outerLogger->updateStatus(Log::SUCCESS);
            return true;
        } elseif (in_array($status, ['FAILURE', 'INTERNAL_ERROR', 'TIMEOUT', 'CANCELLED'])) {
            $this->logger->notice('An error appeared while building the Docker image', [
                'context' => $context,
                'status' => $status
            ]);
            throw new DockerException(
                isset($json['statusDetail']) ? $status . ': ' . $json['statusDetail'] : $status
            );
        }
        return false;
    }
}
