<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Billing\Subscription\InMemorySubscriptionClient;
use ContinuousPipe\Billing\Subscription\Subscription;
use Ramsey\Uuid\Uuid;
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
     * @var InMemorySubscriptionClient
     */
    private $inMemorySubscriptionClient;

    public function __construct(KernelInterface $kernel, InMemorySubscriptionClient $inMemorySubscriptionClient)
    {
        $this->kernel = $kernel;
        $this->inMemorySubscriptionClient = $inMemorySubscriptionClient;
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

    /**
     * @Given the billing account :billingAccountUuid have the following subscriptions:
     */
    public function theBillingAccountHaveTheFollowingSubscriptions($billingAccountUuid, TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->inMemorySubscriptionClient->addSubscription(
                Uuid::fromString($billingAccountUuid),
                new Subscription(
                    Uuid::uuid4(),
                    $row['plan'],
                    $row['state'],
                    (int) $row['quantity'],
                    0,
                    new \DateTime(),
                    new \DateTime()
                )
            );
        }
    }

    /**
     * @Then I should see that my current plan is for :numberOfUsers users
     */
    public function iShouldSeeThatMyCurrentPlanIsForUsers($numberOfUsers)
    {
        $this->assertStatusCode(200);

        $expectedMarkup = 'data-current-plan-quantity="'.$numberOfUsers.'"';
        if (false === strpos($this->response->getContent(), $expectedMarkup)) {
            throw new \RuntimeException('Did not found \''.$expectedMarkup.'\' in the page');
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
