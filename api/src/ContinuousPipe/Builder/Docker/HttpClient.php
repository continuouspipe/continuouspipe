<?php

namespace ContinuousPipe\Builder\Docker;

use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use ContinuousPipe\Builder\Docker\HttpClient\OutputHandler;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Docker\Docker;
use Docker\Manager\ContainerManager;
use Docker\Manager\ImageManager;
use GuzzleHttp\Stream\Stream;
use LogStream\Logger;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class HttpClient implements Client
{
    /**
     * @var Docker
     */
    private $docker;

    /**
     * @var DockerfileResolver
     */
    private $dockerfileResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputHandler
     */
    private $outputHandler;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param Docker             $docker
     * @param DockerfileResolver $dockerfileResolver
     * @param LoggerInterface    $logger
     * @param OutputHandler      $outputHandler
     * @param BucketRepository   $bucketRepository
     */
    public function __construct(Docker $docker, DockerfileResolver $dockerfileResolver, LoggerInterface $logger, OutputHandler $outputHandler, BucketRepository $bucketRepository)
    {
        $this->docker = $docker;
        $this->dockerfileResolver = $dockerfileResolver;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        try {
            return $this->doBuild($archive, $request, $logger);
        } catch (\Http\Client\Exception $e) {
            $this->logger->notice('An error appeared while building an image', [
                'buildRequest' => $request,
                'exception' => $e,
            ]);

            if ($e->getPrevious() instanceof DockerException) {
                throw $e->getPrevious();
            }

            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        try {
            $createImageStream = $this->docker->getImageManager()->push(
                $image->getName(), [
                    'tag' => $image->getTag(),
                    'X-Registry-Auth' => $credentials->getAuthenticationString(),
                ],
                ImageManager::FETCH_STREAM
            );

            $createImageStream->onFrame($this->getOutputCallback($logger));
            $createImageStream->wait();
        } catch (\Http\Client\Exception $e) {
            if ($e->getPrevious() instanceof DockerException) {
                throw $e->getPrevious();
            }

            $this->logger->error('An unexpected error appeared while building an image', [
                'image' => $image,
                'exception' => $e,
            ]);

            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function runAndCommit(Image $image, Logger $logger, $command)
    {
        throw new DockerException('This is a deprecated feature you wouldn\'t have to use anymore');
    }

    /**
     * @param Archive      $archive
     * @param BuildRequest $request
     * @param Logger       $logger
     *
     * @return Image
     */
    private function doBuild(Archive $archive, BuildRequest $request, Logger $logger)
    {
        $image = $request->getImage();

        $parameters = [
            'q' => (int) false,
            't' => $this->getImageName($image),
            'nocache' => (int) false,
            'rm' => (int) false,
            'dockerfile' => $this->dockerfileResolver->getFilePath($request->getContext()),
            'Content-type' => 'application/tar',
            'X-Registry-Config' => $this->generateHttpRegistryConfig($request),
            'pull' => (int) true,
        ];

        $environment = $request->getEnvironment();
        if (!empty($environment)) {
            $parameters['buildargs'] = json_encode($environment);
        }

        $content = $archive->isStreamed() ? new Stream($archive->read()) : $archive->read();

        // Allow a build to be up to half an hour
        $buildStream = $this->docker->getImageManager()->build($content, $parameters, ContainerManager::FETCH_STREAM);
        $buildStream->onFrame($this->getOutputCallback($logger));

        try {
            $buildStream->wait();
        } catch (UnexpectedValueException $e) {
            $this->logger->error('Unexpected exception while waiting for the Docker build', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw new DockerException('Unable to ensure the build was successful: '.$e->getMessage());
        }

        return $image;
    }

    /**
     * @param Image $image
     *
     * @return string
     */
    private function getImageName(Image $image)
    {
        return $image->getName().':'.$image->getTag();
    }

    /**
     * @param BuildRequest $request
     *
     * @return string
     */
    private function generateHttpRegistryConfig(BuildRequest $request)
    {
        $bucket = $this->bucketRepository->find($request->getCredentialsBucket());
        $registryConfig = [];

        foreach ($bucket->getDockerRegistries() as $dockerRegistry) {
            $registryConfig[$dockerRegistry->getServerAddress()] = [
                'username' => $dockerRegistry->getUsername(),
                'password' => $dockerRegistry->getPassword(),
            ];
        }

        if (array_key_exists('docker.io', $registryConfig)) {
            $registryConfig['https://index.docker.io/v1/'] = $registryConfig['docker.io'];
        }

        return base64_encode(json_encode($registryConfig));
    }

    /**
     * Get the client stream callback.
     *
     * @param Logger $logger
     *
     * @return callable
     */
    private function getOutputCallback(Logger $logger)
    {
        return function ($output) use ($logger) {
            $output = $this->outputHandler->handle($output);

            if (!empty($output)) {
                $logger->child(new Text($output));
            }
        };
    }
}
