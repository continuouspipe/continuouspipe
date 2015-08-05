<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use ContinuousPipe\Builder\Tests\Docker\FakeDockerBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;

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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param Kernel $kernel
     * @param FakeDockerBuilder $fakeDockerBuilder
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(Kernel $kernel, FakeDockerBuilder $fakeDockerBuilder, TokenStorageInterface $tokenStorage)
    {
        $this->kernel = $kernel;
        $this->fakeDockerBuilder = $fakeDockerBuilder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new \ContinuousPipe\User\SecurityUser(new \ContinuousPipe\User\User('samuel')));

        $this->tokenStorage->setToken($token);
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
