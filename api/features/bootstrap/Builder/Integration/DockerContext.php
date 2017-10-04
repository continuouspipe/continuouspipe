<?php

namespace Builder\Integration;

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Docker\HttpClient\OutputHandler;
use ContinuousPipe\Builder\Docker\HttpClient\RawOutputHandler;
use Docker\API\Model\ContainerConfig;
use Docker\Docker;

class DockerContext implements Context
{
    /**
     * @var Docker
     */
    private $docker;
    /**
     * @var OutputHandler
     */
    private $outputHandler;

    /**
     * @param Docker $docker
     * @param OutputHandler $outputHandler
     */
    public function __construct(Docker $docker, OutputHandler $outputHandler)
    {
        $this->docker = $docker;
        $this->outputHandler = $outputHandler;
    }

    /**
     * @Then the file :path in the image :image should contain :contents
     */
    public function theFileInTheImageShouldContain($path, $image, $contents)
    {
        $containerManager = $this->docker->getContainerManager();
        $containerConfig = new ContainerConfig();
        $containerConfig->setImage($image);
        $containerConfig->setCmd(['/bin/sh', '-c', 'cat '.$path]);

        $containerCreateResult = $containerManager->create($containerConfig);
        $attachStream = $containerManager->attach($containerCreateResult->getId(), [
            'stream' => true,
            'stdin' => true,
            'stdout' => true,
            'stderr' => true
        ]);

        $containerManager->start($containerCreateResult->getId());
        $output = '';
        $attachStream->onStdout(function ($stdout) use (&$output) {
            $output .= $stdout;
        });
        $attachStream->onStderr(function ($stderr) use (&$output) {
            $output .= $stderr;
        });

        $attachStream->wait();

        if (false === strpos($output, $contents)) {
            throw new \RuntimeException('String not found in '.$output);
        }
    }
}
