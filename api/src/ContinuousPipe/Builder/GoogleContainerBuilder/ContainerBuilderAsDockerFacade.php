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
            $sourceArtifact = $this->writeSourceArtifact($archive);

            $buildIdentifier = $this->requestBuild($context, $sourceArtifact);

            $serviceBuilder = new ServiceBuilder(
                [
                    'projectId' => $this->projectId,
                    'keyFilePath' => $this->serviceAccountPath,
                ]
            );

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

                if ($startTime + (30 * 60) < time()) {
                    throw new DockerException('Build took longer than 30 minutes!');
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

    /**
     * @param $image
     * @return string
     */
    private function getImageName($image)
    {
        return $image->getName() . ':' . $image->getTag();
    }

    private function basicLogger(Logger $logger)
    {
        return function (array $log) use ($logger) {
            if ($log['textPayload'] != self::BOUNDARY.':LOGIN'
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

            if (in_array($log['textPayload'], [self::BOUNDARY . ':LOGIN', self::BOUNDARY . ':BUILD'])) {
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

    /**
     * @param Archive $archive
     * @return array
     * @throws DockerException
     */
    private function writeSourceArtifact(Archive $archive)
    {
        $sourceArtifact = new Artifact(uniqid() . '.tar.gz');

        try {
            $this->artifactManager->write($archive, $sourceArtifact, Archive::TAG_GZ);
            return $sourceArtifact;
        } catch (Artifact\ArtifactException $e) {
            throw new DockerException('Something went wrong while pushing the source artifact', $e->getCode(), $e);
        }
    }

    /**
     * @param BuildContext $context
     * @return Logger
     */
    private function getBuildOuterLogger(BuildContext $context)
    {
        $mainLogger = $this->loggerFactory->fromId($context->getLogStreamIdentifier());
        $buildOuterLogger = $mainLogger->child(
            new Text(
                $context->getImage() === null
                    ? 'Building Docker image'
                    : sprintf('Building Docker image <code>%s</code>', $this->getImageName($context->getImage()))
            )
        )->updateStatus(Log::RUNNING);
        return $buildOuterLogger;
    }

    /**
     * @param BuildContext $context
     * @return string
     */
    private function dockerRegistryCredentials(BuildContext $context)
    {
        return base64_encode(
            \GuzzleHttp\json_encode(
                array_map(
                    function (DockerRegistry $dockerRegistry) {
                        if ('docker.io' == ($serverAddress = $dockerRegistry->getServerAddress())) {
                            $serverAddress = 'https://index.docker.io/v1/';
                        }

                        return [
                            'serveraddress' => $serverAddress,
                            'username' => $dockerRegistry->getUsername(),
                            'password' => $dockerRegistry->getPassword(),
                        ];
                    },
                    $context->getDockerRegistries()
                )
            )
        );
    }

    /**
     * @param BuildContext $context
     * @param $dockerImageName
     * @return array
     */
    private function dockerCommandArguments(BuildContext $context, $dockerImageName)
    {
        $dockerCommandArguments = [
            'build',
            '-t',
            $dockerImageName,
            '--file',
            $this->dockerfileResolver->getFilePath($context->getContext()),
            '--pull'
        ];

        // Add build arguments
        foreach ($context->getEnvironment() as $argumentName => $argumentValue) {
            $dockerCommandArguments[] = '--build-arg';
            $dockerCommandArguments[] = $argumentName . '=' . $argumentValue;
        }

        $dockerCommandArguments[] = '.';
        return $dockerCommandArguments;
    }

    /**
     * @param BuildContext $context
     * @param $sourceArtifact
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function requestBuild(BuildContext $context, $sourceArtifact)
    {
        $dockerImageName = 'docker.io/sroze/foo-bar';
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
                            'name' => 'quay.io/continuouspipe/cloud-builder:v1',
                            'env' => [
                                'DOCKER_REGISTRY_CONFIG=' . $this->dockerRegistryCredentials($context),
                                'PUSH_DOCKER_IMAGE=' . $dockerImageName,
                                'BOUNDARY_STRING=' . self::BOUNDARY
                            ],
                            'args' => $this->dockerCommandArguments($context, $dockerImageName),
                        ]
                    ],
                ]
            ]
        );

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
    private function checkBuildStatus($buildIdentifier, $outerLogger, $context)
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
