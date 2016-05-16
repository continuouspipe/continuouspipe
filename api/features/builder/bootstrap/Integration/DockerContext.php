<?php

namespace Integration;

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Docker\HttpClient\OutputHandler;
use ContinuousPipe\Builder\Docker\HttpClient\RawOutputHandler;
use Docker\Container;
use Docker\Docker;
use Docker\Image;

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
     * @Then the command of the image :name should be :command
     */
    public function theCommandOfTheImageShouldBe($image, $command)
    {
        list($name, $tag) = explode(':', $image);

        $inspection = $this->docker->getImageManager()->inspect(new Image($name, $tag));
        $foundCommand = implode(' ', $inspection['Config']['Cmd']);

        if ($foundCommand != $command) {
            throw new \RuntimeException(sprintf(
                'Found command "%s" while expecting "%s"',
                $foundCommand,
                $command
            ));
        }
    }

    /**
     * @Then the file :path in the image :image should contain :contents
     */
    public function theFileInTheImageShouldContain($path, $image, $contents)
    {
        $containerManager = $this->docker->getContainerManager();
        $container = new Container([
            'Image' => $image,
            'Cmd' => [
                '/bin/sh', '-c', 'cat '.$path,
            ],
        ]);

        $output = '';
        $successful = $containerManager->run($container, function($raw) use (&$output) {
            $output .= $this->outputHandler->handle($raw);
        });

        if (!$successful) {
            throw new \RuntimeException('The command is not successful');
        }

        if (false === strpos($output, $container)) {
            throw new \RuntimeException('String not found in '.$output);
        }
    }
}
