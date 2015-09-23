<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Tests\Docker\TraceableDockerClient;
use ContinuousPipe\User\DockerRegistryCredentials;
use ContinuousPipe\User\Tests\Authenticator\InMemoryAuthenticatorClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;

class BuilderContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TraceableDockerClient
     */
    private $traceableDockerClient;

    /**
     * @var InMemoryAuthenticatorClient
     */
    private $inMemoryAuthenticatorClient;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param Kernel $kernel
     * @param TraceableDockerClient $traceableDockerClient
     * @param TokenStorageInterface $tokenStorage
     * @param InMemoryAuthenticatorClient $inMemoryAuthenticatorClient
     */
    public function __construct(Kernel $kernel, TraceableDockerClient $traceableDockerClient, TokenStorageInterface $tokenStorage, InMemoryAuthenticatorClient $inMemoryAuthenticatorClient)
    {
        $this->kernel = $kernel;
        $this->tokenStorage = $tokenStorage;
        $this->traceableDockerClient = $traceableDockerClient;
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
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
        $this->response = $this->kernel->handle(Request::create(
            '/build',
            'POST', [], [], [], [],
            $requestJson->getRaw()
        ));
    }

    /**
     * @Then the image :name should be built
     */
    public function theImageShouldBeBuilt($name)
    {
        $found = [];
        $buildRequests = $this->traceableDockerClient->getBuilds();

        foreach ($buildRequests as $request) {
            $image = $request->getImage();
            $imageName = sprintf('%s:%s', $image->getName(), $image->getTag());
            if ($imageName == $name) {
                return;
            }

            $found[] = $imageName;
        }

        throw new \RuntimeException(sprintf('Image "%s" not found (but found %s)', $name, implode(', ', $found)));
    }

    /**
     * @Then the image :name should be pushed
     */
    public function theImageShouldBePushed($name)
    {
        $found = [];
        $pushedImages = $this->traceableDockerClient->getPushes();

        foreach ($pushedImages as $image) {
            $imageName = sprintf('%s:%s', $image->getName(), $image->getTag());
            if ($imageName == $name) {
                return;
            }

            $found[] = $imageName;
        }

        throw new \RuntimeException(sprintf('Image "%s" not found (but found %s)', $name, implode(', ', $found)));
    }

    /**
     * @When I send a build request for the fixture repository :repository with the following environment:
     */
    public function iSendABuildRequestForTheFixtureRepositoryWithTheFollowingEnvironment($repository, TableNode $table)
    {
        $environmentVariables = array_reduce($table->getHash(), function($list, $env) {
            $list[$env['name']] = $env['value'];

            return $list;
        }, []);

        $environmentVariablesJson = json_encode($environmentVariables);

        $contents = <<<EOF
{
  "image": {
    "name": "my/image",
    "tag": "master"
  },
  "repository": {
    "address": "fixtures://$repository",
    "branch": "master"
  },
  "environment": $environmentVariablesJson
}
EOF;

        $this->response = $this->kernel->handle(Request::create(
            '/build',
            'POST', [], [], [], [],
            $contents
        ));
    }

    /**
     * @Then the build should be successful
     */
    public function theBuildShouldBeSuccessful()
    {
        if ($this->response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf(
                'Got response code %d, expected 200',
                $this->response->getStatusCode()
            ));
        }

        $json = json_decode($this->response->getContent(), true);
        if (false === $json) {
            throw new \RuntimeException('Found non-JSON response');
        }

        if ($json['status'] != 'success') {
            throw new \RuntimeException(sprintf(
                'Expected status to be successful, but found "%s"',
                $json['status']
            ));
        }
    }

    /**
     * @Given I have docker registry credentials
     */
    public function iHaveDockerRegistryCredentials()
    {
        $this->inMemoryAuthenticatorClient->addDockerCredentials('samuel', new DockerRegistryCredentials('samuel', 'samuel', 'samuel', 'default'));
    }

    /**
     * @Then the build should be errored
     */
    public function theBuildShouldBeErrored()
    {
        $json = json_decode($this->response->getContent(), true);
        if (false === $json) {
            throw new \RuntimeException('Found non-JSON response');
        }

        if (isset($json['status']) && $json['status'] != 'error') {
            throw new \RuntimeException(sprintf(
                'Expected status to be errored, but found "%s"',
                $json['status']
            ));
        }
    }

    /**
     * @Then the command :command should be ran on image :image
     */
    public function theCommandShouldBeRanOnImage($command, $image)
    {
        $found = [];
        $matchingRuns = array_filter($this->traceableDockerClient->getRuns(), function(array $run) use ($command, $image, &$found) {
            /** @var Image $image */
            $foundImage = $run['image'];
            $containerImageName = $foundImage->getName().':'.$foundImage->getTag();

            $found[] = $run['command'];

            return $containerImageName == $image && $command == $run['command'];
        });

        if (0 == count($matchingRuns)) {
            throw new \RuntimeException(sprintf(
                'Found no matching runs, but found commands "%s"',
                implode('", "', $found)
            ));
        }
    }

    /**
     * @Then a container should be committed with the image name :name
     */
    public function aContainerShouldBeCommittedWithTheImageName($name)
    {
        $matchingCommits = array_filter($this->traceableDockerClient->getCommits(), function(array $commit) use ($name) {
            /** @var \ContinuousPipe\Builder\Image $image */
            $image = $commit['image'];
            $imageName = $image->getName().':'.$image->getTag();

            return $imageName == $name;
        });

        if (0 == count($matchingCommits)) {
            throw new \RuntimeException(sprintf(
                'Found no matching commits'
            ));
        }
    }
}
