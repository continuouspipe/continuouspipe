<?php

namespace River;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class ClusterHealthContext implements Context
{

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var HandlerStack
     */
    private $httpHandlerStack;

    /**
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    private $response;


    public function __construct(HandlerStack $httpHandlerStack, KernelInterface $kernel)
    {
        $this->httpHandlerStack = $httpHandlerStack;
        $this->kernel = $kernel;
    }

    /**
     * @Given the cluster with the address :address will have the following problems:
     */
    public function theClusterWithTheAddressWillHaveTheFollowingProblems($address, TableNode $table)
    {
        $this->httpHandlerStack->setHandler(new MockHandler([
            new Response(200, [], json_encode($table->getHash())),
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
