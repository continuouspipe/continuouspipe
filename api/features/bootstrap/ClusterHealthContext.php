<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class ClusterHealthContext implements Context
{
    /**
     * @var Client
     */
    private $clusterHealthCheckHttpClient;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    private $response;

    public function __construct(Client $clusterHealthCheckHttpClient, KernelInterface $kernel)
    {
        $this->clusterHealthCheckHttpClient = $clusterHealthCheckHttpClient;
        $this->kernel = $kernel;
    }

    /**
     * @Given the cluster with the address :address will have the following problems:
     */
    public function theClusterWithTheAddressWillHaveTheFollowingProblems($address, TableNode $table)
    {
        $this->clusterHealthCheckHttpClient->getEmitter()->attach(new Mock([
            new Response(200, [], Stream::factory(json_encode($table->getHash())))
        ]));
    }

    /**
     * @When I ask the alerts of the cluster :cluster of the team :team
     */
    public function iAskTheAlertsOfTheClusterOfTheTeam($cluster, $team)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/teams/'.$team.'/clusters/'.$cluster.'/health'
        ));
    }

    /**
     * @Then I should see the following problems:
     */
    public function iShouldSeeTheFollowingProblems(TableNode $table)
    {
        $expectedProblems = $table->getHash();
        $foundProblems = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if ($expectedProblems != $foundProblems) {
            var_dump($expectedProblems);
            var_dump($foundProblems);
            throw new \RuntimeException('Found problems different than expecting ones');
        }
    }
}
