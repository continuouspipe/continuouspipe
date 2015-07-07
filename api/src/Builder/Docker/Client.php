<?php

namespace Builder\Docker;

use Docker\Docker;
use Builder\Archive;
use Builder\Image;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class Client
{
    /**
     * @var Docker
     */
    private $docker;

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function build(Archive $archive, Image $image)
    {
        $imageName = $image->getName().':'.$image->getTag();
        $this->docker->build($archive, $imageName, $this->getOutputCallback());
    }

    public function push(Image $image)
    {
        $registryAuth = 'ewoJImh0dHBzOi8vaW5kZXguZG9ja2VyLmlvL3YxLyI6IHsKCQkiYXV0aCI6ICJjM0p2ZW1VNk5rUkNTRVk1ZEhRPSIsCgkJImVtYWlsIjogInNhbXVlbC5yb3plQGdtYWlsLmNvbSIKCX0KfQ==';

        $this->docker->getImageManager()->push($image->getName(), $image->getTag(), $registryAuth, $this->getOutputCallback());
    }

    private function getOutputCallback()
    {
        return function($output, $type) {
            var_dump($output, $type);

            if (is_array($output) && array_key_exists('error', $output)) {
                $message = $output['error'];
            } else if (is_array($output) && array_key_exists('stream', $output)) {
                $message = $output['stream'];
            } else if (is_string($output)) {
                $message = $output;
            } else {
                //var_dump($output);
            }

            if (isset($message)) {
                //echo ($type == 2 ? 'OUT' : 'ERR') . ': ' . $message;
            }
        };
    }
}
