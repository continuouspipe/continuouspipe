<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Billing\Subscription\InMemorySubscriptionClient;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\TracedSubscriptionClient;
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
    /**
     * @var TracedSubscriptionClient
     */
    private $tracedSubscriptionClient;

    public function __construct(
        KernelInterface $kernel,
        InMemorySubscriptionClient $inMemorySubscriptionClient,
        TracedSubscriptionClient $tracedSubscriptionClient
    ) {
        $this->kernel = $kernel;
        $this->tracedSubscriptionClient = $tracedSubscriptionClient;
        $this->inMemorySubscriptionClient = $inMemorySubscriptionClient;
    }

    /**
     * @When I configure my billing profile :profileUuid
     */
    public function iConfigureMyBillingProfile($profileUuid)
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profile/' . $profileUuid, 'GET', [], [
            'MOCKSESSID' => $this->kernel->getContainer()->get('session')->getId(),
        ]));
    }

    /**
     * @When I view the list of billing profiles
     */
    public function iViewTheListOfBillingProfiles()
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profiles', 'GET', [], [
            'MOCKSESSID' => $this->kernel->getContainer()->get('session')->getId(),
        ]));
    }

    /**
     * @When I view the billing profile :uuid
     */
    public function iViewTheBillingProfile($uuid)
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profile/' . $uuid, 'GET', [], [
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
     * @Then I should see that one has been created in the name :profileName
     */
    public function iShouldSeeThatOneHasBeenCreatedForMe($profileName)
    {
        $this->assertStatusCode(200);

        if (false === strpos($this->response->getContent(), $profileName . '</a></li>')) {
            throw new \RuntimeException('Did not found \'samuel (samuel.roze@gmail.com)\' in the page');
        }
    }

    /**
     * @When I subscribe for :count users to the profile :uuid
     */
    public function iSubscribeForUsers($count, $uuid)
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profile/' . $uuid, 'POST', [
            'quantity' => $count,
            '_operation' => 'subscribe',
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
                    array_key_exists('uuid', $row) ? Uuid::fromString($row['uuid']) : Uuid::uuid4(),
                    $row['plan'],
                    $row['state'],
                    (int) $row['quantity'],
                    0,
                    new \DateTime('-1 month'),
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

    /**
     * @Then I should see that my current usage is of :users active users
     */
    public function iShouldSeeThatMyCurrentUsageIsOfActiveUsers($numberOfUsers)
    {
        $this->assertStatusCode(200);

        $expectedMarkup = 'data-current-usage="'.$numberOfUsers.'"';
        if (false === strpos($this->response->getContent(), $expectedMarkup)) {
            throw new \RuntimeException('Did not found \''.$expectedMarkup.'\' in the page');
        }
    }

    /**
     * @Then I should be able to cancel my subscription
     */
    public function iShouldBeAbleToCancelMySubscription()
    {
        $this->assertStatusCode(200);

        if (false === strpos($this->response->getContent(), 'data-action="cancel"')) {
            throw new \RuntimeException('Did not found \'data-action="cancel"\' in the page');
        }
    }

    /**
     * @When I cancel my subscription :subscriptionUuid for the profile :profileUuid
     */
    public function iCancelMySubscription($subscriptionUuid, $profileUuid)
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profile/' . $profileUuid, 'POST', [
            '_subscription_uuid' => $subscriptionUuid,
            '_operation' => 'cancel',
        ], [
            'MOCKSESSID' => $this->kernel->getContainer()->get('session')->getId(),
        ]));
    }

    /**
     * @Then the subscription :subscriptionUuid should have been cancelled
     */
    public function theSubscriptionShouldHaveBeenCancelled($subscriptionUuid)
    {
        foreach ($this->tracedSubscriptionClient->getCancelledSubscriptions() as $subscription) {
            if ($subscription->getUuid() == $subscriptionUuid) {
                return;
            }
        }

        throw new \RuntimeException('The subscription was not cancelled');
    }

    /**
     * @When I update my subscription :subscriptionUuid with a quantity of :quantity for the profile :profileUuid
     */
    public function iUpdateMySubscriptionWithAQuantityOf($subscriptionUuid, $quantity, $profileUuid)
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profile/' . $profileUuid, 'POST', [
            '_subscription_uuid' => $subscriptionUuid,
            '_operation' => 'update',
            'quantity' => $quantity,
        ], [
            'MOCKSESSID' => $this->kernel->getContainer()->get('session')->getId(),
        ]));
    }

    /**
     * @Then the subscription :subscriptionUuid should have been updated with a quantity of :quantity
     */
    public function theSubscriptionShouldHaveBeenUpdatedWithAQuantityOf($subscriptionUuid, $quantity)
    {
        foreach ($this->tracedSubscriptionClient->getUpdatedSubscriptions() as $subscription) {
            if ($subscription->getUuid() == $subscriptionUuid) {
                if ($subscription->getQuantity() == $quantity) {
                    return;
                }

                throw new \RuntimeException(sprintf(
                    'Updated with a quantity of %d instead',
                    $subscription->getQuantity()
                ));
            }
        }

        throw new \RuntimeException('The subscription was not updated');
    }

    /**
     * @When I add a billing profile named :profileName
     */
    public function iAddABillingProfileNamed($profileName)
    {
        $this->response = $this->kernel->handle(Request::create('/account/billing-profiles', 'POST', [
            'name' => $profileName
        ], [
            'MOCKSESSID' => $this->kernel->getContainer()->get('session')->getId(),
        ]));
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
