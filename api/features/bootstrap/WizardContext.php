<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubOrganisation;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryCodeRepositoryRepository;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryOrganisationRepository;
use GitHub\WebHook\Model\Organisation;
use GitHub\WebHook\Model\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

class WizardContext implements Context
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
     * @var InMemoryOrganisationRepository
     */
    private $organisationRepository;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param Kernel $kernel
     * @param InMemoryCodeRepositoryRepository $codeRepositoryRepository
     * @param InMemoryOrganisationRepository $organisationRepository
     */
    public function __construct(Kernel $kernel, InMemoryCodeRepositoryRepository $codeRepositoryRepository, InMemoryOrganisationRepository $organisationRepository)
    {
        $this->kernel = $kernel;
        $this->codeRepositoryRepository = $codeRepositoryRepository;
        $this->organisationRepository = $organisationRepository;
    }

    /**
     * @Given I have the following repositories:
     */
    public function iHaveTheFollowingRepositories(TableNode $repositories)
    {
        foreach ($repositories->getHash() as $repository) {
            $this->codeRepositoryRepository->add(GitHubCodeRepository::fromRepository(
                new Repository(new \GitHub\WebHook\Model\User('foo'), $repository['repository'], 'url', false, $repository['repository'])
            ));
        }
    }

    /**
     * @Given the following repositories exist for organisations:
     */
    public function theFollowingRepositoriesExistForOrganisations(TableNode $repositories)
    {
        foreach ($repositories->getHash() as $repository) {
            $this->codeRepositoryRepository->addForOrganisation(GitHubCodeRepository::fromRepository(
                new Repository(new \GitHub\WebHook\Model\User('foo'), $repository['repository'], 'bar', false, $repository['repository'])
            ), $repository['organisation']);
        }
    }

    /**
     * @When I send a request to list my repositories
     */
    public function iSendARequestToListMyRepositories()
    {
        $this->response = $this->kernel->handle(Request::create('/wizard/repositories', 'GET'));
    }

    /**
     * @When I send a request to list repositories of :organisation
     */
    public function iSendARequestToListRepositoriesOf($organisation)
    {
        $this->response = $this->kernel->handle(Request::create(sprintf('/wizard/organisations/%s/repositories', $organisation), 'GET'));
    }

    /**
     * @Then I should receive the following list of repositories:
     */
    public function iShouldReceiveTheFollowingListOfRepositories(TableNode $expected)
    {
        $received = array_map(function ($repository) {
            return $repository['name'];
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
            throw new \Exception('Received more repositories than expected.');
        }
    }

    /**
     * @Given I am a member of the following organisations:
     */
    public function iAmAMemberOfTheFollowingOrganisations(TableNode $organisations)
    {
        foreach ($organisations->getHash() as $organisation) {
            $this->organisationRepository->add(new GitHubOrganisation(
                new Organisation($organisation['organisation'], 'url')
            ));
        }
    }

    /**
     * @When I send a request to list my organisations
     */
    public function iSendARequestToListMyOrganisations()
    {
        $this->response = $this->kernel->handle(Request::create('/wizard/organisations', 'GET'));
    }

    /**
     * @Then I should receive the following list of organisations:
     */
    public function iShouldReceiveTheFollowingListOfOrganisations(TableNode $expected)
    {
        $received = array_map(function ($organisation) {
            return $organisation['organisation']['login'];
        }, json_decode($this->response->getContent(), true));

        foreach ($expected->getHash() as $organisation) {
            if (!in_array($organisation['organisation'], $received)) {
                throw new \Exception("Have not received organisation '$organisation'");
            }
        }
    }
}
