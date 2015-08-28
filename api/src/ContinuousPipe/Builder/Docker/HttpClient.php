<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\BuildRequest;
use Docker\Docker;
use Docker\Exception\UnexpectedStatusCodeException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;
use LogStream\Logger;
use LogStream\Node\Text;

class HttpClient implements Client
{
    /**
     * @var Docker
     */
    private $docker;

    /**
     * @param Docker $docker
     */
    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        $this->doBuild($archive, $request, $this->getOutputCallback($logger));
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
        } catch (UnexpectedStatusCodeException $e) {
            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        } catch (RequestException $e) {
            throw new DockerException($e->getMessage(), $e->getCode(), $e);
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
            if (is_array($output)) {
                if (array_key_exists('error', $output)) {
                    throw new DockerException($output);
                } else if (array_key_exists('stream', $output)) {
                    $output = $output['stream'];
                } else if (array_key_exists('status', $output)) {
                    $output = $output['status'];
                }
            }

            if (!is_string($output)) {
                $output = 'Unknown ('.gettype($output).'): '.print_r($output, true);
            }

            if (!empty($output)) {
                $logger->append(new Text($output));
            }
        };
    }

    /**
     * @param Archive      $archive
     * @param BuildRequest $request
     * @param callable     $callback
     *
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    private function doBuild(Archive $archive, BuildRequest $request, callable $callback)
    {
        $image = $request->getImage();
        $imageName = $image->getName().':'.$image->getTag();

        $options = [
            'q' => (integer) false,
            't' => $imageName,
            'nocache' => (integer) false,
            'rm' => (integer) false,
        ];

        $dockerFilePath = $request->getContext() ? $request->getContext()->getDockerFilePath() : null;
        if (!empty($dockerFilePath)) {
            $options['dockerfile'] = $dockerFilePath;
        }

        $content = $archive->isStreamed() ? new Stream($archive->read()) : $archive->read();

        return $this->docker->getHttpClient()->post(['/build{?data*}', ['data' => $options]], [
            'headers' => ['Content-Type' => 'application/tar'],
            'body' => $content,
            'stream' => true,
            'callback' => $callback,
            'wait' => true,
        ]);
    }
}
