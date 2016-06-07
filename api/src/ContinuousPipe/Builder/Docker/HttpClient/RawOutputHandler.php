<?php

namespace ContinuousPipe\Builder\Docker\HttpClient;

use ContinuousPipe\Builder\Docker\DockerException;
use Docker\API\Model\BuildInfo;
use Docker\API\Model\PushImageInfo;
use Psr\Log\LoggerInterface;

class RawOutputHandler implements OutputHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($output)
    {
        if ($output instanceof BuildInfo) {
            if (!empty($error = $output->getError())) {
                throw new DockerException($error);
            }

            return $output->getStream();
        }

        if ($output instanceof PushImageInfo) {
            if (!empty($error = $output->getError())) {
                throw new DockerException($error);
            }

            if (null !== ($progressDetails = $output->getProgressDetail())) {
                return $progressDetails->getMessage();
            }

            return $output->getProgress();
        }

        $rawOutput = $output;

        if (is_array($output)) {
            if (array_key_exists('error', $output)) {
                if (!is_string($output['error'])) {
                    $this->logger->warning('Unknown error output found in Docker stream', [
                        'output' => $output,
                    ]);

                    $output['error'] = print_r($output, true);
                }

                throw new DockerException($output['error']);
            } elseif (array_key_exists('stream', $output)) {
                $output = $output['stream'];
            } elseif (array_key_exists('status', $output)) {
                $output = $output['status'];
            }
        }

        if (null !== $output && !is_string($output)) {
            $this->logger->warning('Unknown output found in Docker stream', [
                'output' => $rawOutput,
            ]);

            return '';
        }

        return $output;
    }
}
