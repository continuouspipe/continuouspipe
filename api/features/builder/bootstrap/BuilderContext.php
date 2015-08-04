<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use ContinuousPipe\Builder\Tests\Docker\FakeDockerBuilder;

class BuilderContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var FakeDockerBuilder
     */
    private $fakeDockerBuilder;

    /**
     * @param Kernel $kernel
     * @param FakeDockerBuilder $fakeDockerBuilder
     */
    public function __construct(Kernel $kernel, FakeDockerBuilder $fakeDockerBuilder)
    {
        $this->kernel = $kernel;
        $this->fakeDockerBuilder = $fakeDockerBuilder;
    }

    /**
     * @When I send the following build request:
     */
    public function iSendTheFollowingBuildRequest(PyStringNode $requestJson)
    {
        $response = $this->kernel->handle(Request::create(
            '/build',
            'POST', [], [], [], [],
            $requestJson->getRaw()
        ));

        if ($response->getStatusCode() !== 200) {
            echo ($response->getContent());
            throw new \RuntimeException(sprintf(
                'Got response code %d, expected 200',
                $response->getStatusCode()
            ));
        }
    }

    /**
     * @Then the image :name should be built
     */
    public function theImageShouldBeBuilt($name)
    {
        $found = [];

        foreach ($this->fakeDockerBuilder->getBuilds() as $build) {
            $image = $build->getRequest()->getImage();
            $imageName = sprintf('%s:%s', $image->getName(), $image->getTag());
            if ($imageName == $name) {
                return;
            }

            $found[] = $imageName;
        }

        throw new \RuntimeException(sprintf('Image "%s" not found (but found %s)', $name, implode(', ', $found)));
    }
}
