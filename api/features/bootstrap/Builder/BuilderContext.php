<?php

namespace Builder;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Aggregate\BuildRepository;
use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStep;
use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStepRepository;
use ContinuousPipe\Builder\Aggregate\Command\StartBuild;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildStepStarted;
use ContinuousPipe\Builder\Aggregate\FromEvents\BuildEventStreamResolver;
use ContinuousPipe\Builder\Archive\FileSystemArchive;
use ContinuousPipe\Builder\Article\TraceableArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\GoogleContainerBuilder\HttpGoogleContainerBuildClient;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Image\Registry;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\Tests\Docker\TraceableDockerClient;
use ContinuousPipe\Builder\Tests\TraceableArtifactManager;
use ContinuousPipe\Builder\Tests\TraceableBuildCreator;
use ContinuousPipe\Events\AggregateNotFound;
use ContinuousPipe\Events\EventStore\EventStore;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Tolerance\Metrics\Publisher\TracedPublisher;
use JMS\Serializer\SerializerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Tolerance\Metrics\Metric;
use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\Security\User\User;
use Symfony\Component\HttpFoundation\Response;
use Ramsey\Uuid\Uuid;
use ContinuousPipe\Builder\Client\BuilderException;

class BuilderContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var SecurityContext
     */
    private $securityContext;

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
     * @var Response|null
     */
    private $response;
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
    /**
     * @var BuildFactory
     */
    private $buildFactory;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var BuildRequestTransformer
     */
    private $buildRequestTransformer;

    /**
     * @var TracedPublisher
     */
    private $tracedPublisher;
    /**
     * @var TraceableArtifactManager
     */
    private $traceableArtifactManager;
    /**
     * @var TraceableBuildCreator
     */
    private $traceableBuildCreator;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var BuilderClient
     */
    private $builderClient;

    public function __construct(
        Kernel $kernel,
        TraceableDockerClient $traceableDockerClient,
        InMemoryAuthenticatorClient $inMemoryAuthenticatorClient,
        TraceableArchiveBuilder $traceableArchiveBuilder,
        BuildRepository $buildRepository,
        BuildStepRepository $buildStepRepository,
        BuildFactory $buildFactory,
        SerializerInterface $serializer,
        MessageBus $commandBus,
        EventStore $eventStore,
        BuildRequestTransformer $buildRequestTransformer,
        TracedPublisher $tracedPublisher,
        TraceableArtifactManager $traceableArtifactManager,
        TraceableBuildCreator $traceableBuildCreator,
        Registry $registry,
        BuilderClient $builderClient
    ) {
        $this->kernel = $kernel;
        $this->traceableDockerClient = $traceableDockerClient;
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
        $this->traceableArchiveBuilder = $traceableArchiveBuilder;
        $this->buildRepository = $buildRepository;
        $this->buildStepRepository = $buildStepRepository;
        $this->buildFactory = $buildFactory;
        $this->serializer = $serializer;
        $this->commandBus = $commandBus;
        $this->eventStore = $eventStore;
        $this->buildRequestTransformer = $buildRequestTransformer;
        $this->tracedPublisher = $tracedPublisher;
        $this->traceableArtifactManager = $traceableArtifactManager;
        $this->traceableBuildCreator = $traceableBuildCreator;
        $this->registry = $registry;
        $this->builderClient = $builderClient;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->securityContext = $scope->getEnvironment()->getContext('Builder\SecurityContext');
    }

    /**
     * @Given there is a build :identifier with the following request:
     */
    public function thereIsABuildWithTheFollowingRequest($identifier, PyStringNode $string)
    {
        $this->buildFactory->fromRequest(
            $this->buildRequestTransformer->transform(
                $this->serializer->deserialize($string->getRaw(), BuildRequest::class, 'json')
            ),
            $identifier
        );
    }

    /**
     * @Given there is a build :identifier
     * @Given there is a build :identifier with the engine :engine
     * @When I create a build :identifier
     * @When I create a build :identifier with the engine :engine
     */
    public function thereIsABuild($identifier, $engine = null)
    {
        $this->securityContext->iAmAuthenticated();
        $this->securityContext->thereIsTheBucket('00000000-0000-0000-0000-000000000000');
        $this->securityContext->theBucketContainsTheFollowingDockerRegistryCredentials('00000000-0000-0000-0000-000000000000', [
            new DockerRegistry('sroze', 'password', 'my@email.com', 'docker.io'),
        ]);

        $request = <<<CONTENT
{
  "steps": [
    {
      "image": {
        "name": "sroze/php-example",
        "tag": "continuous"
      },
      "repository": {
        "address": "fixtures://php-example",
        "branch": "747850e8c821a443a7b5cee28a48581069049739"
      }
    }
  ],
  "credentialsBucket": "00000000-0000-0000-0000-000000000000"
}
CONTENT;

        $request = $this->serializer->deserialize($request, BuildRequest::class, 'json');
        if (null !== $engine) {
            $request = $request->withEngine(new Engine($engine));
        }

        $this->buildFactory->fromRequest(
            $this->buildRequestTransformer->transform($request),
            $identifier
        );
    }

    /**
     * @Given the build :identifier was started
     */
    public function theBuildWasStarted($identifier)
    {
        $event = new BuildStarted($identifier);

        $this->eventStore->store(
            (new BuildEventStreamResolver())->streamByEvent($event),
            $event
        );
    }

    /**
     * @Given the build :identifier step #:stepIndex was started
     */
    public function theBuildStepWasStarted($identifier, $stepIndex)
    {
        $event = new BuildStepStarted($identifier, (int) $stepIndex, new BuildStepConfiguration());

        $this->eventStore->store(
            (new BuildEventStreamResolver())->streamByEvent($event),
            $event
        );
    }

    /**
     * @When I start the build :identifier
     */
    public function iStartTheBuild($identifier)
    {
        $this->commandBus->handle(new StartBuild(
            $identifier
        ));
    }

    /**
     * @When I send the following build request:
     */
    public function iSendTheFollowingBuildRequest(PyStringNode $requestJson)
    {
        $this->createAndStartBuild($requestJson->getRaw());
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

        $this->createAndStartBuild($contents);
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
     * @Given the :name parameter is set to :value
     */
    public function theParameterIsSetTo($name, $value)
    {
        if ($value != $this->kernel->getContainer()->getParameter($name)) {
            throw new \RuntimeException(
                sprintf(
                    'Test expect to have "%s" parameter set to "%s", but it is "%s".',
                    $name,
                    $value,
                    $this->kernel->getContainer()->getParameter($name)
                )
            );
        }
    }

    /**
     * @Then I should see the metrics published as below:
     */
    public function iShouldSeeTheMetricsPublishedAsBelow(TableNode $table)
    {
        $allMetrics = $this->tracedPublisher->getPublishedMetrics();
        foreach ($table->getColumnsHash() as $row) {
            $sumOfMetrics = $this->sumMetricsByName($allMetrics, $row['metric']);
            if ($sumOfMetrics != $row['value']) {
                throw new \UnexpectedValueException(
                    sprintf('The sum of values for metric %s is "%f", but "%f" expected.',
                        $row['metric'],
                        $sumOfMetrics,
                        $row['value']
                    )
                );
            }
        }
    }

    /**
     * @Then the manifest file should be sent in an archive to to Google Cloud Storage
     */
    public function theManifestFileShouldBeSentInAnArchiveToToGoogleCloudStorage()
    {
        if (!isset($this->traceableArtifactManager->getWritten()[0]) || !$this->traceableArtifactManager->getWritten()[0]['archive']->contains(HttpGoogleContainerBuildClient::MANIFEST_FILENAME)) {
            throw new \RuntimeException('Archive did not contain Manifest file');
        }
    }

    /**
     * @Then the archive details should be sent to Google Cloud Builder
     */
    public function theArchiveDetailsShouldBeSentToGoogleCloudBuilder()
    {
        if (!isset($this->traceableBuildCreator->getRequests()[0]) || $this->traceableBuildCreator->getRequests()[0]['source'] != $this->traceableArtifactManager->getWritten()[0]['archive']->contains(HttpGoogleContainerBuildClient::MANIFEST_FILENAME)) {
            throw new \RuntimeException('Archive not send to GCB when starting build');
        }
    }

    /**
     * @Then the archive details should not be sent to Google Cloud Builder
     */
    public function theArchiveDetailsShouldNotBeSentToGoogleCloudBuilder()
    {
        if (isset($this->traceableBuildCreator->getRequests()[0])) {
            throw new \RuntimeException('Archive was sent to GCB');
        }
    }

    /**
     * @Given the image :image exists in the docker registry
     */
    public function theImageExistsInTheDockerRegistry($image)
    {
        $this->registry->addImage($image);
    }

    private function createAndStartBuild(string $requestAsJson)
    {
        $request = $this->serializer->deserialize($requestAsJson, BuildRequest::class, 'json');

        try {
            $build = $this->builderClient->build($request);
            $this->response = Response::create($this->serializer->serialize($build, 'json'));
        } catch (BuilderException $e) {
            $this->response = Response::create(json_encode([
                'message' => $e->getMessage(),
            ]), 400);
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

    private function sumMetricsByName(array $allMetrics, string $name): float
    {
        $metricsForName = array_filter($allMetrics, function(Metric $metric) use ($name) {
            return $name == $metric->getName();
        });

        $metricValuesForName = array_map(function(Metric $metric) {
            return $metric->getValue();
        }, $metricsForName);

        return array_sum($metricValuesForName);
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
