<?php

namespace ContinuousPipe\Builder\Docker\HttpClient;

use ContinuousPipe\Builder\Docker\DockerException;

class RawOutputHandler implements OutputHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle($output)
    {
        if (is_array($output)) {
            if (array_key_exists('error', $output)) {
                if (!is_string($output['error'])) {
                    $output['error'] = 'Stringified error: '.print_r($output, true);
                }

                throw new DockerException($output['error']);
            } elseif (array_key_exists('stream', $output)) {
                $output = $output['stream'];
            } elseif (array_key_exists('status', $output)) {
                $output = $output['status'];
            }
        }

        if (null !== $output && !is_string($output)) {
            $output = 'Unknown ('.gettype($output).')';
        }

        return $output;
    }
}
