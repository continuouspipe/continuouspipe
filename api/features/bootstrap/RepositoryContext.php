<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use GitHub\WebHook\Model\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class RepositoryContext implements Context
{
    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var InMemoryCodeRepositoryRepository
     */
    private $codeRepositoryRepository;
    /**
     * @var Response
     */
    private $response;

    /**
     * @param Kernel $kernel
     * @param InMemoryCodeRepositoryRepository $codeRepositoryRepository
     */
    public function __construct(
        Kernel $kernel,
        InMemoryCodeRepositoryRepository $codeRepositoryRepository)
    {
        $this->kernel = $kernel;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
    }

    /**
     * @Given I have the following repositories:
     */
    public function iHaveTheFollowingRepositories(TableNode $repositories)
    {
        foreach ($repositories->getHash() as $repository) {
            $this->codeRepositoryRepository->add(new GitHubCodeRepository(
                new Repository($repository['repository'], 'url', false, $repository['repository'])
            ));
        }
    }

    /**
     * @Given the following repositories exist for organisations:
     */
    public function theFollowingRepositoriesExistForOrganisations(TableNode $repositories)
    {
        foreach ($repositories->getHash() as $repository) {
            $this->codeRepositoryRepository->addForOrganisation(new GitHubCodeRepository(
                new Repository($repository['repository'], 'bar', false, $repository['repository'])
            ), $repository['organisation']);
        }
    }

    /**
     * @When I send a request to list my repositories
     */
    public function iSendARequestToListMyRepositories()
    {
        $this->response = $this->kernel->handle(Request::create('/user-repositories', 'GET'));
    }

    /**
     * @When I send a request to list repositories of :organisation
     */
    public function iSendARequestToListRepositoriesOf($organisation)
    {
        $this->response = $this->kernel->handle(Request::create("/user-repositories/organisation/$organisation", 'GET'));
    }

    /**
     * @Then I should receive the following list of repositories:
     */
    public function iShouldReceiveTheFollowingListOfRepositories(TableNode $expected)
    {
        $received = array_map(function($repository) {
            return $repository['repository']['name'];
        }, json_decode($this->response->getContent(), true));

        foreach ($expected->getHash() as $repository) {
            $key = array_search($repository['repository'], $received);

            if ($key === false) {
                throw new \Exception("Have not received repository '{$repository['repository']}'");
            } else {
                unset($received[$key]);
            }
        }

        if (count($received)) {
            throw new \Exception("Received more repositories than expected.");
        }
    }
}
