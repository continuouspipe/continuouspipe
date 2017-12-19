<?php

namespace Pipe;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Pipe\Client\Client;
use ContinuousPipe\Pipe\Client\PipeClientException;
use ContinuousPipe\Pipe\DeploymentRequest\Target;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\HookableNamespaceRepository;
use ContinuousPipe\Pipe\Uuid\UuidTransformer;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use function GuzzleHttp\Promise\unwrap;
use JMS\Serializer\SerializerInterface;
use Kubernetes\Client\Exception\ServerError;
use Kubernetes\Client\Model\Status;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ContinuousPipe\Pipe\EventBus\EventStore;
use Ramsey\Uuid\Uuid;

class EnvironmentContext implements Context
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @var HookableNamespaceRepository
     */
    private $hookableNamespaceRepository;

    /**
     * @var JWTManagerInterface
     */
    private $jwtManager;
    /**
     * @var Client
     */
    private $pipeClient;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(
        EventStore $eventStore,
        DeploymentRepository $deploymentRepository,
        MessageBus $eventBus,
        HookableNamespaceRepository $hookableNamespaceRepository,
        JWTManagerInterface $jwtManager,
        Client $pipeClient,
        SerializerInterface $serializer,
        TeamRepository $teamRepository
    ) {
        $this->eventStore = $eventStore;
        $this->deploymentRepository = $deploymentRepository;
        $this->eventBus = $eventBus;
        $this->hookableNamespaceRepository = $hookableNamespaceRepository;
        $this->jwtManager = $jwtManager;
        $this->pipeClient = $pipeClient;
        $this->serializer = $serializer;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @When I request the environment list of the cluster :cluster of the team :team
     */
    public function iRequestTheEnvironmentListOfTheCluster($cluster, $team)
    {
        try {
            $environments = $this->pipeClient->getEnvironments($cluster, $this->teamRepository->find($team))->wait();
            $this->response = Response::create($this->serializer->serialize($environments, 'json'));
        } catch (PipeClientException $e) {
            $this->response = Response::create(json_encode([
                'message' => $e->getMessage(),
            ]), 400);
        }
    }

    /**
     * @When I request the environment list of the cluster :cluster of the team :team that have the labels :labels
     */
    public function iRequestTheEnvironmentListOfTheClusterOfTheTeamThatHaveTheLabels($cluster, $team, $labels)
    {
        $labelsFilters = [];
        foreach (explode(',', $labels) as $label) {
            list($key, $value) = explode('=', $label);

            $labelsFilters[$key] = $value;
        }

        try {
            $environments = $this->pipeClient->getEnvironmentsLabelled($cluster, $this->teamRepository->find($team), $labelsFilters)->wait();
            $this->response = Response::create($this->serializer->serialize($environments, 'json'));
        } catch (PipeClientException $e) {
            $this->response = Response::create(json_encode([
                'message' => $e->getMessage(),
            ]), 400);
        }
    }

    /**
     * @When I delete the environment named :environment of the cluster :cluster of the team :team
     */
    public function iDeleteTheEnvironmentNamedOfTheClusterOfTheTeam($environment, $cluster, $team)
    {
        try {
            $this->pipeClient->deleteEnvironment(new Target($environment, $cluster), $this->teamRepository->find($team), new User('foo', Uuid::uuid4()));
            $this->response = Response::create('', Response::HTTP_NO_CONTENT);
        } catch (PipeClientException $e) {
            $this->response = Response::create(json_encode([
                'message' => $e->getMessage(),
            ]), 400);
        }
    }

    /**
     * @When I delete the pod named :podName for the team :teamName and the cluster :clusterId
     */
    public function iDeleteThePodNamedForTheTeamAndTheCluster($podName, $teamName, $clusterId)
    {
        try {
            $this->pipeClient->deletePod(
                $this->teamRepository->find($teamName),
                new User('foo', Uuid::uuid4()),
                $clusterId,
                'namespace',
                $podName
            );
            $this->response = Response::create('', Response::HTTP_NO_CONTENT);
        } catch (PipeClientException $e) {
            $this->response = Response::create(json_encode([
                'message' => $e->getMessage(),
            ]), 400);
        }
    }

    /**
     * @Then the namespace should be deleted successfully
     * @Then the namespace :namespace should be deleted successfully
     */
    public function theNamespaceShouldBeDeletedSuccessfully($namespace = null)
    {
        if (!in_array($this->response->getStatusCode(), [200, 204])) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected the status code 200 or 204 but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then the validation should fail
     */
    public function theValidationShouldFail()
    {
        if ($this->response->getStatusCode() !== 400) {
            echo $this->response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected the response to be 400, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then I should be told that I am forbidden to see these environments
     */
    public function iShouldBeToldThatIAmForbiddenToSeeTheseEnvironments()
    {
        if (!in_array($this->response->getStatusCode(), [403, 401])) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected the response to be 403/401, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then I should see the component :name
     */
    public function iShouldSeeTheComponentInEnvironment($name)
    {
        $this->getComponentFromListResponse($name);
    }

    /**
     * @Then the status of the component :name should contain the public endpoint :endpoint
     */
    public function theStatusOfTheComponentShouldContainThePublicEndpoint($name, $endpoint)
    {
        $component = $this->getComponentFromListResponse($name);

        $matchingEndpoints = $this->getMatchingEndpoints($component, $endpoint);

        if (!count($matchingEndpoints)) {
            var_dump($component['status']);

            throw new \RuntimeException('Public endpoint was not found');
        }
    }

    /**
     * @Then the status of the component :name should not contain the public endpoint :endpoint
     */
    public function theStatusOfTheComponentShouldNotContainThePublicEndpoint($name, $endpoint)
    {
        $component = $this->getComponentFromListResponse($name);

        $matchingEndpoints = $this->getMatchingEndpoints($component, $endpoint);

        if (count($matchingEndpoints) !== 0) {
            var_dump($component['status']);

            throw new \RuntimeException('Public endpoint was found');
        }
    }

    /**
     * @Then the status of the component :component should contain container :containerName
     */
    public function theStatusOfTheComponentShouldContainContainer($name, $containerName)
    {
        $component = $this->getComponentFromListResponse($name);
        $matchingContainers = array_filter($component['status']['containers'], function(array $container) use ($containerName) {
            return $container['identifier'] = $containerName;
        });

        if (count($matchingContainers) == 0) {
            var_dump($component['status']['containers']);

            throw new \RuntimeException('Found no matching container');
        }
    }

    /**
     * @Then the status of the component :name should be :status
     */
    public function theStatusOfTheComponentShouldBe($name, $status)
    {
        $component = $this->getComponentFromListResponse($name);
        $foundStatus = $component['status']['status'];

        if ($foundStatus != $status) {
            throw new \RuntimeException(sprintf(
                'Found status "%s" while expecting "%s"',
                $foundStatus,
                $status
            ));
        }
    }

    /**
     * @Then I should see the environment :identifier
     */
    public function iShouldSeeTheEnvironment($identifier)
    {
        $environments = $this->getEnvironmentsFromResponse();
        $matchingEnvironments = array_filter($environments, function(array $environment) use ($identifier) {
            return $environment['identifier'] == $identifier;
        });

        if (count($matchingEnvironments) == 0) {
            throw new \RuntimeException('No matching environments found');
        }
    }

    /**
     * @Then I should not see the environment :identifier
     */
    public function iShouldNotSeeTheEnvironment($identifier)
    {
        $environments = $this->getEnvironmentsFromResponse();
        $matchingEnvironments = array_filter($environments, function(array $environment) use ($identifier) {
            return $environment['identifier'] == $identifier;
        });

        if (count($matchingEnvironments) > 0) {
            throw new \RuntimeException(sprintf('Found %d matching environments, while expecting 0', count($matchingEnvironments)));
        }
    }

    /**
     * @Given the environment API calls to the cluster failed
     */
    public function theEnvironmentAPICallsToTheClusterFailed()
    {
        $faultGenerator = function() {
            throw new ServerError(new Status(Status::UNKNOWN, 'This error is intentional.'));
        };

        $this->hookableNamespaceRepository->addFindAllHook($faultGenerator);
        $this->hookableNamespaceRepository->addFindByLabelsHook($faultGenerator);
        $this->hookableNamespaceRepository->addFindOneByNameHook($faultGenerator);
    }

    /**
     * @Then I should receive a service unavailable error
     */
    public function iShouldReceiveAServiceUnavailableError()
    {
        if (Response::HTTP_SERVICE_UNAVAILABLE != $this->response->getStatusCode()) {
            throw new \RuntimeException(
                sprintf('Unexpected to get HTTP status code %d returned.', $this->response->getStatusCode())
            );
        }
    }

    /**
     * @Then the resources of the component :component should have the following :attribute:
     */
    public function theResourcesOfTheComponentShouldHaveTheFollowing($component, $attribute, TableNode $table)
    {
        $specification = $this->getComponentFromListResponse($component)['specification'];

        foreach ($table->getHash() as $tableRow) {
            if (
                !isset($specification['resources'][$attribute][$tableRow['type']])
                ||
                $specification['resources'][$attribute][$tableRow['type']] != $tableRow['value']
            ) {
                throw new \RuntimeException(
                    sprintf($tableRow['type'] . ' ' . $attribute .' %s not found.', $tableRow['value'])
                );
            }
        }

    }

    /**
     * @param string $name
     * @return array
     */
    private function getComponentFromListResponse($name)
    {
        $environments = $this->getEnvironmentsFromResponse();

        foreach ($environments as $environment) {
            $components = $environment['components'];
            $matchingComponents = array_filter($components, function ($component) use ($name) {
                return $component['name'] == $name;
            });

            if (0 < count($matchingComponents)) {
                return current($matchingComponents);
            }
        }

        throw new \RuntimeException(sprintf('No component named "%s" found in the environment', $name));
    }

    /**
     * @return array
     */
    private function getEnvironmentsFromResponse()
    {
        if ($this->response->getStatusCode() !== 200) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected response code 200, got %d',
                $this->response->getStatusCode()
            ));
        }

        $environments = json_decode($this->response->getContent(), true);
        if (!is_array($environments)) {
            throw new \RuntimeException('Expecting an array, got something else');
        }

        return $environments;
    }

    private function getMatchingEndpoints($component, $endpoint)
    {
        return array_filter(
            $component['status']['public_endpoints'],
            function ($publicEndpoint) use ($endpoint) {
                return $publicEndpoint == $endpoint;
            }
        );

    }
}
