<?php

namespace Mock;

use Behat\Behat\Context\Context;

class DockerContext implements Context
{
    /**
     * @Then the command of the image :name should be :command
     */
    public function theCommandOfTheImageShouldBe($image, $command)
    {
    }
}
