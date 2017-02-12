<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildRepository;
use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStep;
use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStepRepository;
use ContinuousPipe\Builder\Archive\FileSystemArchive;
use ContinuousPipe\Builder\Article\TraceableArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Notifier\HookableNotifier;
use ContinuousPipe\Builder\Notifier\NotificationException;
use ContinuousPipe\Builder\Notifier\TraceableNotifier;
use ContinuousPipe\Builder\Tests\Docker\TraceableDockerClient;
use ContinuousPipe\Events\AggregateNotFound;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

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
     * @var BuildRepository
     */
    private $buildRepository;
    /**
     * @var BuildStepRepository
     */
    private $buildStepRepository;

    public function __construct(
        Kernel $kernel,
        TraceableDockerClient $traceableDockerClient,
        InMemoryAuthenticatorClient $inMemoryAuthenticatorClient,
        TraceableNotifier $traceableNotifier,
        HookableNotifier $hookableNotifier,
        TraceableArchiveBuilder $traceableArchiveBuilder,
        BuildRepository $buildRepository,
        BuildStepRepository $buildStepRepository
    ) {
        $this->kernel = $kernel;
        $this->traceableDockerClient = $traceableDockerClient;
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
        $this->traceableNotifier = $traceableNotifier;
        $this->hookableNotifier = $hookableNotifier;
        $this->traceableArchiveBuilder = $traceableArchiveBuilder;
        $this->buildRepository = $buildRepository;
        $this->buildStepRepository = $buildStepRepository;
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
     * @Then the request should be refused with a :statusCode status code
     */
    public function theRequestShouldBeRefusedWithAStatusCode($statusCode)
    {
        $this->assertResponseCode((int) $statusCode);
    }

    /**
     * @Then the response should contain the following JSON:
     */
    public function theResponseShouldContainTheFollowingJson(PyStringNode $string)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $expectedConfiguration = \GuzzleHttp\json_decode($string->getRaw(), true);

        $intersection = array_intersect_recursive($expectedConfiguration, $json);

        if ($intersection != $expectedConfiguration) {
            throw new \RuntimeException(sprintf(
                'Expected to have at least this JSON but found: %s',
                PHP_EOL.$this->response->getContent()
            ));
        }
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
     * @When I send a build request for the image :image from the fixture repository :repository with the following environment:
     */
    public function iSendABuildRequestForTheFixtureRepositoryWithTheFollowingEnvironment($image, $repository, TableNode $table)
    {
        $environmentVariables = array_reduce($table->getHash(), function($list, $env) {
            $list[$env['name']] = $env['value'];

            return $list;
        }, []);

        $environmentVariablesJson = json_encode($environmentVariables);

        $contents = <<<EOF
{
  "image": {
    "name": "$image",
    "tag": "latest"
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
        $this->assertResponseCode(200);

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
     * @Then the build should be failed
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
        $requests = $this->traceableArchiveBuilder->getSteps();
        $matchingRequests = array_filter($requests, function(BuildStepConfiguration $step) use ($token) {
            return $step->getRepository()->getToken() == $token;
        });

        if (count($matchingRequests) == 0) {
            throw new \RuntimeException('No matching request with this token');
        }
    }

    /**
     * @Then the archive should contain the file :file
     */
    public function theArchiveShouldContainTheFile($file)
    {
        $archives = $this->traceableArchiveBuilder->getArchives();
        if (count($archives) != 1) {
            throw new \RuntimeException(sprintf(
                'Expected 1 archive, found %d',
                count($archives)
            ));
        }

        $archive = $archives[0];
        if (!$archive instanceof FileSystemArchive) {
            throw new \RuntimeException('Do not support non-filesystem archives yet');
        }

        $filePath = $archive->getDirectory().DIRECTORY_SEPARATOR.$file;
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf(
                'File "%s" do not exists (looked in: %s)',
                $file,
                $archive->getDirectory()
            ));
        }
    }

    /**
     * @Then the step #:stepIdentifier should be :status
     */
    public function theStepShouldBeFailed($stepIdentifier, $status)
    {
        $foundStatus = $this->getBuildStep($stepIdentifier)->getStatus();

        if ($foundStatus != $status) {
            throw new \RuntimeException(sprintf(
                'Found status "%s" while expecting "%s"',
                $foundStatus,
                $status
            ));
        }
    }

    /**
     * @Then the step #:stepIdentifier should not be started
     */
    public function theStepShouldNotBeStarted($stepIdentifier)
    {
        try {
            $this->getBuildStep($stepIdentifier);

            throw new \RuntimeException('Step is found apparently');
        } catch (AggregateNotFound $e) {
            // Great.
        }
    }

    /**
     * @param int $status
     */
    private function assertResponseCode($status)
    {
        if ($this->response->getStatusCode() !== $status) {
            echo $this->response->getContent();
            throw new \RuntimeException(sprintf(
                'Got response code %d, expected %d',
                $this->response->getStatusCode(),
                $status
            ));
        }
    }

    private function getBuild() : Build
    {
        $this->assertResponseCode(200);
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        return $this->buildRepository->find($json['uuid']);
    }

    private function getBuildStep(int $stepPosition) : BuildStep
    {
        return $this->buildStepRepository->find(
            $this->getBuild()->getIdentifier(),
            $stepPosition
        );
    }
}

function array_intersect_recursive($array1, $array2)
{
    foreach($array1 as $key => $value)
    {
        if (!isset($array2[$key]))
        {
            unset($array1[$key]);
        }
        else
        {
            if (is_array($array1[$key]))
            {
                $array1[$key] = array_intersect_recursive($array1[$key], $array2[$key]);
            }
            elseif ($array2[$key] !== $value)
            {
                unset($array1[$key]);
            }
        }
    }
    return $array1;
}
