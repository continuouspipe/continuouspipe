<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Authenticator\Obfuscate\Serializer\ObfuscateCredentialsSubscriber;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

class CredentialsBucketContext implements Context
{
    /**
     * @var \TeamContext
     */
    private $teamContext;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param Kernel $kernel
     * @param BucketRepository $bucketRepository
     */
    public function __construct(Kernel $kernel, BucketRepository $bucketRepository)
    {
        $this->kernel = $kernel;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->teamContext = $scope->getEnvironment()->getContext('TeamContext');
    }

    /**
     * @Given the user :username have access to the bucket :uuid
     */
    public function theUserHaveAccessToTheBucket($username, $uuid)
    {
        $this->thereIsABucket($uuid);
        $this->teamContext->thereIsATeam($username);
        $this->teamContext->theUserIsInTheTeam($username, $username);
        $this->teamContext->theBucketOfTheTeamIsThe($username, $uuid);
    }

    /**
     * @Given there is a bucket :uuid
     */
    public function thereIsABucket($uuid)
    {
        $this->bucketRepository->save(new Bucket(Uuid::fromString($uuid)));
    }

    /**
     * @Given I have the following docker registry credentials in the bucket :bucket:
     */
    public function iHaveTheFollowingDockerRegistryCredentials($bucket, TableNode $table)
    {
        $this->iCreateANewDockerRegistryWithTheFollowingConfiguration($bucket, $table);
        $this->assertResponseCodeIs($this->response, 201);
    }

    /**
     * @Given I have the following GitHub tokens in the bucket :bucket:
     */
    public function iHaveTheFollowingGithubTokensInTheBucket($bucket, TableNode $table)
    {
        $this->iCreateAGithubTokenWithTheFollowingConfigurationInTheBucket($bucket, $table);
        $this->assertResponseCodeIs($this->response, 201);
    }

    /**
     * @Given I have the following clusters in the bucket :bucket:
     */
    public function iHaveTheFollowingClustersInTheBucket($bucket, TableNode $table)
    {
        $this->iCreateAClusterWithTheFollowingConfigurationInTheBucket($bucket, $table);
        $this->assertResponseCodeIs($this->response, 201);
    }

    /**
     * @When I create a new docker registry with the following configuration in the bucket :bucket:
     */
    public function iCreateANewDockerRegistryWithTheFollowingConfiguration($bucket, TableNode $table)
    {
        $content = json_encode($table->getHash()[0]);

        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/docker-registries', $bucket),
            'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        ));
    }
    /**
     * @When I delete the credentials of the docker registry :serverAddress from the bucket :bucket
     */
    public function iDeleteTheCredentialsOfTheDockerRegistry($serverAddress, $bucket)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/docker-registries/%s', $bucket, urlencode($serverAddress)),
            'DELETE'
        ));

        $this->assertResponseCodeIs($this->response, Response::HTTP_NO_CONTENT);
    }

    /**
     * @When I ask the list of the docker registry credentials in the bucket :bucket
     */
    public function iAskTheListOfTheDockerRegistryCredentialsInTheBucket($bucket)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/docker-registries', $bucket),
            'GET'
        ));
    }

    /**
     * @When I ask the list of the docker registry credentials in the bucket :bucket with the API key :key
     */
    public function iAskTheListOfTheDockerRegistryCredentialsInTheBucketWithTheApiKey($bucket, $key)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/docker-registries', $bucket),
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $key
            ]
        ));
    }

    /**
     * @When I ask the details of the bucket :bucket
     */
    public function iAskTheDetailsOfTheBucket($bucket)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s', $bucket),
            'GET'
        ));
    }

    /**
     * @When I create a GitHub token with the following configuration in the bucket :bucket:
     */
    public function iCreateAGithubTokenWithTheFollowingConfigurationInTheBucket($bucket, TableNode $table)
    {
        $content = json_encode($table->getHash()[0]);

        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/github-tokens', $bucket),
            'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        ));
    }

    /**
     * @When I ask the list of the GitHub tokens in the bucket :bucket
     */
    public function iAskTheListOfTheGithubTokensInTheBucket($bucket)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/github-tokens', $bucket),
            'GET'
        ));
    }

    /**
     * @When I ask the list of the GitHub tokens in the bucket :bucket with the API key :key
     */
    public function iAskTheListOfTheGithubTokensInTheBucketWithTheApiKey($bucket, $key)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/github-tokens', $bucket),
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $key
            ]
        ));
    }

    /**
     * @When I delete the GitHub token of :identifier from the bucket :bucket
     */
    public function iDeleteTheGithubTokenOfFromTheBucket($identifier, $bucket)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/github-tokens/%s', $bucket, $identifier),
            'DELETE'
        ));
    }

    /**
     * @When I create a cluster with the following configuration in the bucket :bucket:
     */
    public function iCreateAClusterWithTheFollowingConfigurationInTheBucket($bucket, TableNode $table)
    {
        $content = json_encode($table->getHash()[0]);

        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/clusters', $bucket),
            'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        ));
    }

    /**
     * @When I ask the list of the clusters in the bucket :bucket
     */
    public function iAskTheListOfTheClustersInTheBucket($bucket)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/clusters', $bucket),
            'GET'
        ));
    }

    /**
     * @When I ask the list of the clusters in the bucket :bucket with the API key :key
     */
    public function iAskTheListOfTheClustersInTheBucketWithTheApiKey($bucket, $key)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/clusters', $bucket),
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $key
            ]
        ));
    }

    /**
     * @When I delete the cluster :identifier from the bucket :bucket
     */
    public function iDeleteTheClusterFromTheBucket($identifier, $bucket)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/api/bucket/%s/clusters/%s', $bucket, $identifier),
            'DELETE'
        ));
    }

    /**
     * @When the list should not contain the cluster :identifier
     */
    public function theListShouldNotContainTheCluster($identifier)
    {
        try {
            $this->theListShouldContainTheCluster($identifier);
            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found) {
            throw new \Exception('The cluster was found');
        }
    }

    /**
     * @Then the list should contain the cluster :identifier
     */
    public function theListShouldContainTheCluster($identifier)
    {
        $decoded = json_decode($this->response->getContent(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Expected to get an array in the JSON response');
        }

        $matchingClusters = array_filter($decoded, function(array $row) use ($identifier) {
            return $row['identifier'] == $identifier;
        });

        if (0 == count($matchingClusters)) {
            throw new \RuntimeException('No matching cluster found');
        }
    }

    /**
     * @Then the new cluster should have been saved successfully
     */
    public function theNewClusterShouldHaveBeenSavedSuccessfully()
    {
        $this->assertResponseCodeIs($this->response, 201);
    }

    /**
     * @Then the new cluster should not have been saved successfully
     */
    public function theNewClusterShouldNotHaveBeenSavedSuccessfully()
    {
        $this->assertResponseCodeIs($this->response, 400);
    }

    /**
     * @Then the list should not contain the access token :identifier
     */
    public function theListShouldNotContainTheAccessToken($identifier)
    {
        try {
            $this->theListShouldContainTheAccessToken($identifier);
            $found = true;
        } catch (\RuntimeException $e) {
            $found = false;
        }

        if ($found) {
            throw new \RuntimeException('Found token');
        }
    }

    /**
     * @Then the list should contain the access token :identifier
     */
    public function theListShouldContainTheAccessToken($identifier)
    {
        $decoded = json_decode($this->response->getContent(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Expected to get an array in the JSON response');
        }

        $matchingCredentials = array_filter($decoded, function(array $row) use ($identifier) {
            return $row['identifier'] == $identifier;
        });

        if (0 == count($matchingCredentials)) {
            throw new \RuntimeException('No matching credentials found');
        }
    }

    /**
     * @Then the new credentials should have been saved successfully
     */
    public function theNewCredentialsShouldHaveBeenSavedSuccessfully()
    {
        $this->assertResponseCodeIs($this->response, 201);
    }

    /**
     * @Then I should receive a bad request error
     */
    public function iShouldReceiveABadRequestError()
    {
        $this->assertResponseCodeIs($this->response, 400);
    }

    /**
     * @Then I should be told that I don't have the authorization for this bucket
     */
    public function iShouldBeToldThatIDonTHaveTheAuthorizationForThisBucket()
    {
        $this->assertResponseCodeIs($this->response, 403);
    }

    /**
     * @Then I should receive a list
     */
    public function iShouldReceiveAList()
    {
        $this->assertResponseCodeIs($this->response, 200);

        $decoded = json_decode($this->response->getContent(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Expected to get an array in the JSON response');
        }
    }

    /**
     * @Then the list should contain the credential for server :serverAddress
     */
    public function theListShouldContainTheCredentialForServer($serverAddress)
    {
        $decoded = json_decode($this->response->getContent(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Expected to get an array in the JSON response');
        }

        $matchingCredentials = array_filter($decoded, function(array $row) use ($serverAddress) {
            return $row['serverAddress'] == $serverAddress;
        });

        if (0 == count($matchingCredentials)) {
            throw new \RuntimeException('No matching credentials found');
        }
    }

    /**
     * @Then I should see the list of the docker registry credentials
     */
    public function iShouldSeeTheListOfTheDockerRegistryCredentials()
    {
        $this->assertResponseCodeIs($this->response, 200);
    }

    /**
     * @Then the :key should be obfuscated in the list items
     */
    public function theShouldBeObfuscatedInTheListItems($key)
    {
        $this->assertResponseCodeIs($this->response, 200);

        $items = \GuzzleHttp\json_decode($this->response->getContent(), true);
        foreach ($items as $item) {
            $value = $item[$key];
            $expected = ObfuscateCredentialsSubscriber::OBFUSCATE_PLACEHOLDER;

            if ($value != $expected) {
                throw new \RuntimeException(sprintf(
                    'Expected the value %s but got %s',
                    $expected,
                    $value
                ));
            }
        }
    }

    /**
     * @Then the :key should not be obfuscated in the list items
     */
    public function theShouldNotBeObfuscatedInTheListItems($key)
    {
        $this->assertResponseCodeIs($this->response, 200);

        $items = \GuzzleHttp\json_decode($this->response->getContent(), true);
        foreach ($items as $item) {
            $value = $item[$key];
            $unexpected = ObfuscateCredentialsSubscriber::OBFUSCATE_PLACEHOLDER;

            if ($value == $unexpected) {
                throw new \RuntimeException(sprintf(
                    'Got %s',
                    $value
                ));
            }
        }
    }

    /**
     * @Then the list should not contain the credential for server :serverAddress
     */
    public function theListShouldNotContainTheCredentialForServer($serverAddress)
    {
        try {
            $this->theListShouldContainTheCredentialForServer($serverAddress);
            $contains = true;
        } catch (\RuntimeException $e) {
            $contains = false;
        }

        if ($contains) {
            throw new \RuntimeException(sprintf(
                'Found a credential for server address "%s"',
                $serverAddress
            ));
        }
    }

    /**
     * @param Response $response
     * @param int $statusCode
     */
    private function assertResponseCodeIs(Response $response, $statusCode)
    {
        if ($response->getStatusCode() != $statusCode) {
            echo $response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected to get status code %d, got %d',
                $statusCode,
                $response->getStatusCode()
            ));
        }
    }
}