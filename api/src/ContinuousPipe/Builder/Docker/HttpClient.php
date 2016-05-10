<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Docker\HttpClient\OutputHandler;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Docker\Container;
use Docker\Context\Context;
use Docker\Docker;
use GuzzleHttp\Exception\RequestException;
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
            return $this->doBuild($archive, $request, $this->getOutputCallback($logger));
        } catch (RequestException $e) {
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
            $this->docker->getImageManager()->push(
                $image->getName(), $image->getTag(),
                $credentials->getAuthenticationString(),
                $this->getOutputCallback($logger)
            );
        } catch (\Docker\Exception $e) {
            $this->logger->error('An error appeared while pushing an image', [
                'image' => $image,
                'exception' => $e,
            ]);

            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        } catch (RequestException $e) {
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
        // Memorize the previous image command
        $imageManager = $this->docker->getImageManager();
        $imageInspection = $imageManager->inspect(new \Docker\Image($image->getName(), $image->getTag()));
        $imageCommands = $imageInspection['Config']['Cmd'];

        // Run a commit the command in the image
        $image = $this->doRunAndCommitCommand($image, $logger, $command);

        // Restore previous image command
        $imageName = $this->getImageName($image);
        $command = '["'.implode('", "', $imageCommands).'"]';
        $dockerFile = <<<EOF
FROM $imageName

CMD $command
EOF;

        $directory = $this->getTemporaryDirectoryPath('restore');
        file_put_contents($directory.DIRECTORY_SEPARATOR.'Dockerfile', $dockerFile);

        try {
            $this->docker->build(new Context($directory), $imageName);
        } catch (RequestException $e) {
            throw new DockerException(
                sprintf('Unable to restore image commands: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $image;
    }

    /**
     * @param Image  $image
     * @param Logger $logger
     * @param string $command
     *
     * @return Image
     *
     * @throws DockerException
     */
    private function doRunAndCommitCommand(Image $image, Logger $logger, $command)
    {
        $containerManager = $this->docker->getContainerManager();
        $container = new Container([
            'Image' => $this->getImageName($image),
            'Cmd' => [
                '/bin/sh', '-c', $command,
            ],
        ]);

        try {
            $this->logger->debug('Running a container', [
                'container' => $container,
            ]);

            $successful = $containerManager->run($container, $this->getOutputCallback($logger));
            if (!$successful) {
                throw new DockerException(sprintf(
                    'Expected exit code 0, but got %d',
                    $container->getExitCode()
                ));
            }

            $this->logger->debug('Committing a container', [
                'container' => $container,
                'image' => $image,
            ]);

            $this->commit($container, $image);
        } catch (\Docker\Exception $e) {
            $this->logger->warning('An error appeared while running container', [
                'container' => $container,
                'exception' => $e,
            ]);

            throw new DockerException(sprintf(
                'Unable to run container: %s',
                $e->getMessage()
            ), $e->getCode(), $e);
        } finally {
            try {
                if ($container->getId()) {
                    $containerManager->remove($container, true);
                }
            } catch (\Exception $e) {
                $this->logger->warning('An error appeared while removing a container', [
                    'container' => $container,
                    'image' => $image,
                    'exception' => $e,
                ]);
            }
        }

        return $image;
    }

    /**
     * Commit the given container.
     *
     * @param Container $container
     * @param Image     $image
     *
     * @return \Docker\Image
     *
     * @throws DockerException
     */
    private function commit(Container $container, Image $image)
    {
        try {
            return $this->docker->commit($container, [
                'repo' => $image->getName(),
                'tag' => $image->getTag(),
            ]);
        } catch (\Docker\Exception $e) {
            throw new DockerException(
                sprintf('Unable to commit container: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
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

    /**
     * @param Archive      $archive
     * @param BuildRequest $request
     * @param callable     $callback
     *
     * @return Image
     */
    private function doBuild(Archive $archive, BuildRequest $request, callable $callback)
    {
        $image = $request->getImage();

        $options = [
            'q' => (integer) false,
            't' => $this->getImageName($image),
            'nocache' => (integer) false,
            'rm' => (integer) false,
            'dockerfile' => $this->dockerfileResolver->getFilePath($request->getContext()),
        ];

        $environment = $request->getEnvironment();
        if (!empty($environment)) {
            $options['buildargs'] = json_encode($environment);
        }

        $content = $archive->isStreamed() ? new Stream($archive->read()) : $archive->read();

        // Allow a build to be up to half an hour
        $client = $this->docker->getHttpClient();
        $client->setDefaultOption('timeout', 1800);
        $client->post(['/build{?data*}', ['data' => $options]], [
            'headers' => [
                'Content-Type' => 'application/tar',
                'X-Registry-Config' => $this->generateHttpRegistryConfig($request),
            ],
            'body' => $content,
            'stream' => true,
            'callback' => $callback,
            'wait' => true,
        ]);

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
     * @param string $prefix
     *
     * @return string
     *
     * @throws DockerException
     */
    private function getTemporaryDirectoryPath($prefix = 'gha')
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid($prefix);
        if (!mkdir($path)) {
            throw new DockerException('Unable to create a temporary directory');
        }

        return $path;
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
}
