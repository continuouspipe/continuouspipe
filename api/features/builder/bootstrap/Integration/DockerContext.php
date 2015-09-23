<?php

namespace Integration;

use Behat\Behat\Context\Context;
use Docker\Docker;
use Docker\Image;

class DockerContext implements Context
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
}