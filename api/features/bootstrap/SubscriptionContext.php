<?php

use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class SubscriptionContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When I configure my billing profile
     */
    public function iConfigureMyBillingProfile()
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profile', 'GET', [], [
            'MOCKSESSID' => $this->kernel->getContainer()->get('session')->getId(),
        ]));
    }

    /**
     * @Then I should be able to subscribe
     */
    public function iShouldBeAbleToSubscribe()
    {
        $this->assertStatusCode(200);

        if (false === strpos($this->response->getContent(), 'data-action="subscribe"')) {
            throw new \RuntimeException('Did not found \'data-action="subscribe"\' in the page');
        }
    }

    /**
     * @When I subscribe for :count users
     */
    public function iSubscribeForUsers($count)
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profile', 'POST', [
            'quantity' => $count,
        ], [
            'MOCKSESSID' => $this->kernel->getContainer()->get('session')->getId(),
        ]));
    }

    /**
     * @Then I should be redirected to the Recurly subscription page of the account :accountUuid
     */
    public function iShouldBeRedirectedToTheRecurlySubscriptionPageOfTheAccount($accountUuid)
    {
        $this->assertStatusCode(302);
        if (null === ($location = $this->response->headers->get('Location'))) {
            throw new \RuntimeException('Did not found any `Location` header in the request');
        }

        if (!preg_match('#^https://continuouspipe.recurly.com/subscribe/[a-z-]+/'.$accountUuid.'/#', $location)) {
            throw new \RuntimeException(sprintf(
                'The location "%s" is not matching the expected expression',
                $location
            ));
        }
    }

    private function assertStatusCode(int $code)
    {
        if ($this->response->getStatusCode() != $code) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status %d but got %d',
                $code,
                $this->response->getStatusCode()
            ));
        }
    }
}
