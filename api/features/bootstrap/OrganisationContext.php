<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubOrganisation;
use ContinuousPipe\River\Tests\CodeRepository\InMemoryOrganisationRepository;
use ContinuousPipe\User\Tests\Authenticator\InMemoryAuthenticatorClient;
use GitHub\WebHook\Model\Organisation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class OrganisationContext implements Context
{
    /**
     * @var Kernel
     */
    private $kernel;
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
     * @param InMemoryOrganisationRepository $organisationRepository
     */
    public function __construct(
        Kernel $kernel,
        InMemoryOrganisationRepository $organisationRepository)
    {
        $this->kernel = $kernel;
        $this->organisationRepository = $organisationRepository;
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
        $this->response = $this->kernel->handle(Request::create('/user-organisations', 'GET'));
    }

    /**
     * @Then I should receive the following list of organisations:
     */
    public function iShouldReceiveTheFollowingListOfOrganisations(TableNode $expected)
    {
        $received = array_map(function($organisation) {
            return $organisation['organisation']['login'];
        }, json_decode($this->response->getContent(), true));

        foreach ($expected->getHash() as $organisation) {
            if (! in_array($organisation['organisation'], $received)) {
                throw new \Exception("Have not received organisation '$organisation'");
            }
        }
    }
}
