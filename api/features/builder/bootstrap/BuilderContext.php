<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Builder\Article\TraceableArchiveBuilder;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Notifier\HookableNotifier;
use ContinuousPipe\Builder\Notifier\NotificationException;
use ContinuousPipe\Builder\Notifier\TraceableNotifier;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Tests\Docker\TraceableDockerClient;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use LogStream\EmptyLogger;
use Ramsey\Uuid\Uuid;
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
     * @var TraceableDockerClient
     */
    private $traceableDockerClient;

    /**
     * @var InMemoryAuthenticatorClient
     */
    private $inMemoryAuthenticatorClient;

    /**
     * @var TraceableNotifier
     */
    private $traceableNotifier;

    /**
     * @var Response|null
     */
    private $response;
    /**
     * @var HookableNotifier
     */
    private $hookableNotifier;
    /**
     * @var TraceableArchiveBuilder
     */
    private $traceableArchiveBuilder;

    /**
     * @param Kernel $kernel
     * @param TraceableDockerClient $traceableDockerClient
     * @param InMemoryAuthenticatorClient $inMemoryAuthenticatorClient
     * @param TraceableNotifier $traceableNotifier
     * @param HookableNotifier $hookableNotifier
     * @param TraceableArchiveBuilder $traceableArchiveBuilder
     */
    public function __construct(Kernel $kernel, TraceableDockerClient $traceableDockerClient, InMemoryAuthenticatorClient $inMemoryAuthenticatorClient, TraceableNotifier $traceableNotifier, HookableNotifier $hookableNotifier, TraceableArchiveBuilder $traceableArchiveBuilder)
    {
        $this->kernel = $kernel;
        $this->traceableDockerClient = $traceableDockerClient;
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
        $this->traceableNotifier = $traceableNotifier;
        $this->hookableNotifier = $hookableNotifier;
        $this->traceableArchiveBuilder = $traceableArchiveBuilder;
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
  "credentialsBucket": "00000000-0000-0000-0000-000000000000",
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
            echo $this->response->getContent();
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
            echo $this->response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected status to be successful, but found "%s"',
                $json['status']
            ));
        }
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
            /** @var Image $foundImage */
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

    /**
     * @Then the notification should be sent
     */
    public function theNotificationShouldBeSent()
    {
        $notifications = $this->traceableNotifier->getNotifications();

        if (count($notifications) == 0) {
            throw new \RuntimeException('No notifications sent');
        }
    }

    /**
     * @Given the notification will fail the first :count times
     */
    public function theNotificationWillFailTheFirstTimes($count)
    {
        $this->hookableNotifier->addHook(function() use (&$count) {
            if ($count-- > 0) {
                throw new NotificationException('This intentionally failed');
            }

            return func_get_args();
        });
    }

    /**
     * @Then the archive should be downloaded using the token :token
     */
    public function theArchiveShouldBeDownloadedUsingTheToken($token)
    {
        $requests = $this->traceableArchiveBuilder->getRequests();
        $matchingRequests = array_filter($requests, function(BuildRequest $request) use ($token) {
            return $request->getRepository()->getToken() == $token;
        });

        if (count($matchingRequests) == 0) {
            throw new \RuntimeException('No matching request with this token');
        }
    }
}
