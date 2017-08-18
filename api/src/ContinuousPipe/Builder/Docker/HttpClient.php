<?php

namespace ContinuousPipe\Builder\Docker;

use Docker\API\Model\ContainerConfig;
use Docker\API\Model\ContainerCreateResult;
use Docker\Stream\TarStream;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamWrapper;
use Http\Client\Exception\RequestException;
use LogStream\LoggerFactory;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use ContinuousPipe\Builder\Docker\HttpClient\OutputHandler;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Docker\Docker;
use Docker\Manager\ImageManager;
use LogStream\Logger;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class HttpClient implements DockerFacade, DockerImageReader
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
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Docker $docker
     * @param DockerfileResolver $dockerfileResolver
     * @param LoggerInterface $logger
     * @param OutputHandler $outputHandler
     * @param BucketRepository $bucketRepository
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        Docker $docker,
        DockerfileResolver $dockerfileResolver,
        LoggerInterface $logger,
        OutputHandler $outputHandler,
        BucketRepository $bucketRepository,
        LoggerFactory $loggerFactory
    ) {
        $this->docker = $docker;
        $this->dockerfileResolver = $dockerfileResolver;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
        $this->bucketRepository = $bucketRepository;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildContext $context, Archive $archive) : Image
    {
        try {
            return $this->doBuild($context, $archive);
        } catch (\Http\Client\Exception $e) {
            $this->logger->notice('An error appeared while building the Docker image', [
                'context' => $context,
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
    public function push(PushContext $context, Image $image)
    {
        try {
            $createImageStream = $this->docker->getImageManager()->push(
                $image->getName(),
                [
                    'tag' => $image->getTag(),
                    'X-Registry-Auth' => $context->getCredentials()->getAuthenticationString(),
                ],
                ImageManager::FETCH_STREAM
            );

            $createImageStream->onFrame($this->getOutputCallback($context));
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
     * @param BuildContext $context
     * @param Archive      $archive
     *
     * @throws DockerException
     *
     * @return Image
     */
    private function doBuild(BuildContext $context, Archive $archive)
    {
        $image = $context->getImage();

        $parameters = [
            'q' => (int) false,
            't' => $this->getImageName($image),
            'nocache' => (int) false,
            'rm' => (int) false,
            'dockerfile' => $this->dockerfileResolver->getFilePath($context->getContext()),
            'Content-type' => 'application/tar',
            'X-Registry-Config' => $this->generateHttpRegistryConfig($context),
            'pull' => (int) true,
        ];

        $environment = $context->getEnvironment();
        if (!empty($environment)) {
            $parameters['buildargs'] = json_encode($environment);
        }

        $content = $archive->isStreamed() ? new TarStream($archive->read()) : $archive->read();

        // Allow a build to be up to half an hour
        $buildStream = $this->docker->getImageManager()->build($content, $parameters, ImageManager::FETCH_STREAM);
        $buildStream->onFrame($this->getOutputCallback($context));

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
     * @param BuildContext $context
     *
     * @return string
     */
    private function generateHttpRegistryConfig(BuildContext $context)
    {
        $registryConfig = [];

        foreach ($context->getDockerRegistries() as $dockerRegistry) {
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
     * @param DockerContext $context
     *
     * @return callable
     */
    private function getOutputCallback(DockerContext $context)
    {
        $logger = $this->loggerFactory->fromId($context->getLogStreamIdentifier());

        return function ($output) use ($logger) {
            $output = $this->outputHandler->handle($output);

            if (!empty($output)) {
                $logger->child(new Text($output));
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function read(Image $image, string $path): Archive
    {
        $containerManager = $this->docker->getContainerManager();

        $containerConfig = new ContainerConfig();
        $containerConfig->setImage($image->getName().':'.$image->getTag());
        $containerConfig->setCmd(['echo']);

        $containerCreateResult = $containerManager->create($containerConfig);
        if (!$containerCreateResult instanceof ContainerCreateResult) {
            throw new DockerException('Something went wrong while creating the container');
        }

        $identifier = $containerCreateResult->getId();

        try {
            $response = $containerManager->getArchive($identifier, [
                'path' => $path,
            ]);
        } catch (RequestException $e) {
            if ($e->getCode() == 404) {
                $message = sprintf('The path "%s" is not found in the Docker container', $path);
            } else {
                $message = 'Unable to read the artifact from the container: '.$e->getMessage();
            }

            throw new DockerException($message, $e->getCode(), $e);
        }

        return Archive\FileSystemArchive::fromStream(
            StreamWrapper::getResource($response->getBody())
        );
    }
}
