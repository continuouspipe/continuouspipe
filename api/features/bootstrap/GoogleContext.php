<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\PredefinedRequestMappingMiddleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class GoogleContext implements Context
{
    /**
     * @var PredefinedRequestMappingMiddleware
     */
    private $predefinedRequestMappingMiddleware;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    private $response;

    /**
     * @param PredefinedRequestMappingMiddleware $predefinedRequestMappingMiddleware
     * @param KernelInterface $kernel
     */
    public function __construct(PredefinedRequestMappingMiddleware $predefinedRequestMappingMiddleware, KernelInterface $kernel)
    {
        $this->predefinedRequestMappingMiddleware = $predefinedRequestMappingMiddleware;
        $this->kernel = $kernel;
    }

    /**
     * @Given the Google account :account have the following Google Compute projects:
     */
    public function theGoogleAccountHaveTheFollowingGoogleComputeProjects($account, TableNode $table)
    {
        $this->predefinedRequestMappingMiddleware->addMapping([
            'method' => 'GET',
            'path' => '#^/v1beta1/projects$#',
            'response' => new Response(200, [], json_encode([
                'projects' => $table->getHash(),
            ]))
        ]);
    }

    /**
     * @When I request the list of Google project for the account :account
     */
    public function iRequestTheListOfGoogleProjectForTheAccount($account)
    {
        $this->response = $this->kernel->handle(Request::create('/api/accounts/' . $account . '/google/projects'));
    }

    /**
     * @Then I should see the project :projectId
     */
    public function iShouldSeeTheProject($projectId)
    {
        if ($this->response->getStatusCode() != 200) {
            echo $this->response->getContent();

            throw new \RuntimeException('Expected to see response 200');
        }

        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingProjects = array_filter($json, function(array $project) use ($projectId) {
            return $project['projectId'] == $projectId;
        });

        if (count($matchingProjects) == 0) {
            throw new \RuntimeException('No matching project found');
        }
    }
}
